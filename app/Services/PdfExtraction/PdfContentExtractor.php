<?php

namespace App\Services\PdfExtraction;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Smalot\PdfParser\Parser;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessStartFailedException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

class PdfContentExtractor
{
    public function extract(string $path): string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            throw new RuntimeException('The uploaded PDF could not be found in public storage.');
        }

        $localPath = $disk->path($path);

        try {
            $content = (new Parser)->parseFile($localPath)->getText();
        } catch (Throwable $exception) {
            throw new RuntimeException(
                'The PDF could not be read. Check that it is a valid, unencrypted PDF.',
                previous: $exception,
            );
        }

        if ($this->containsReadableText($content)) {
            return trim($content);
        }

        return $this->extractWithOcr($localPath);
    }

    private function containsReadableText(string $content): bool
    {
        return preg_match('/[\p{L}\p{N}]{5}/u', $content) === 1;
    }

    private function extractWithOcr(string $pdfPath): string
    {
        $pdfInfoBinary = (string) config('pdf_extraction.pdfinfo_binary');
        $pdfToPpmBinary = (string) config('pdf_extraction.pdftoppm_binary');
        $tesseractBinary = (string) config('pdf_extraction.tesseract_binary');
        $finder = new ExecutableFinder;

        $pdfInfoPath = $this->resolveExecutable($pdfInfoBinary, $finder);
        $pdfToPpmPath = $this->resolveExecutable($pdfToPpmBinary, $finder);
        $tesseractPath = $this->resolveExecutable($tesseractBinary, $finder);
        $missingTools = [];

        foreach ([
            [$pdfInfoPath, 'Poppler pdfinfo'],
            [$pdfToPpmPath, 'Poppler pdftoppm'],
            [$tesseractPath, 'Tesseract OCR'],
        ] as [$binary, $name]) {
            if ($binary === null) {
                $missingTools[] = $name;
            }
        }

        if ($missingTools !== []) {
            throw new RuntimeException(
                'This PDF appears to be scanned and needs OCR. Install '.implode(', ', $missingTools).' and configure their executable paths in config/pdf_extraction.php.',
            );
        }

        try {
            $pageInfo = new Process([$pdfInfoPath, $pdfPath]);
            $pageInfo->setTimeout(30);
            $pageInfo->mustRun();
        } catch (ProcessStartFailedException|ProcessFailedException $exception) {
            throw new RuntimeException('Could not inspect the scanned PDF before OCR.', previous: $exception);
        }

        if (preg_match('/^Pages:\s*(\d+)/mi', $pageInfo->getOutput(), $matches) !== 1) {
            throw new RuntimeException('Could not determine the scanned PDF page count for OCR.');
        }

        $pageCount = (int) $matches[1];
        $maxPages = max(1, (int) config('pdf_extraction.max_ocr_pages', 100));

        if ($pageCount > $maxPages) {
            throw new RuntimeException("The scanned PDF has {$pageCount} pages; OCR is limited to {$maxPages} pages.");
        }

        $temporaryDirectory = storage_path('app/pdf-extraction-ocr/'.Str::uuid());
        File::ensureDirectoryExists($temporaryDirectory);
        $pages = [];

        try {
            for ($page = 1; $page <= $pageCount; $page++) {
                $imagePrefix = $temporaryDirectory.DIRECTORY_SEPARATOR.'page-'.$page;
                $render = new Process([
                    $pdfToPpmPath,
                    '-f',
                    (string) $page,
                    '-l',
                    (string) $page,
                    '-r',
                    '200',
                    '-png',
                    '-singlefile',
                    $pdfPath,
                    $imagePrefix,
                ]);
                $render->setTimeout(120);

                try {
                    $render->mustRun();
                } catch (ProcessStartFailedException|ProcessFailedException $exception) {
                    throw new RuntimeException("Could not render PDF page {$page} for OCR.", previous: $exception);
                }

                $imagePath = $imagePrefix.'.png';

                if (! is_file($imagePath)) {
                    throw new RuntimeException("The PDF renderer did not produce an image for page {$page}.");
                }

                $ocr = new Process([
                    $tesseractPath,
                    $imagePath,
                    'stdout',
                    '-l',
                    (string) config('pdf_extraction.ocr_language', 'eng'),
                    '--psm',
                    '6',
                ]);
                $ocr->setTimeout(180);

                try {
                    $ocr->mustRun();
                } catch (ProcessStartFailedException|ProcessFailedException $exception) {
                    throw new RuntimeException("Tesseract could not read PDF page {$page}.", previous: $exception);
                }

                $pages[] = $ocr->getOutput();
            }
        } finally {
            File::deleteDirectory($temporaryDirectory);
        }

        $content = trim(implode("\n", $pages));

        if (! $this->containsReadableText($content)) {
            throw new RuntimeException('OCR completed, but no readable text was found in the scanned PDF.');
        }

        return $content;
    }

    private function resolveExecutable(string $binary, ExecutableFinder $finder): ?string
    {
        return is_file($binary) ? $binary : $finder->find($binary);
    }
}
