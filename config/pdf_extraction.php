<?php

$popplerBinDirectory = null;
$localAppData = getenv('LOCALAPPDATA');

if (PHP_OS_FAMILY === 'Windows' && $localAppData !== false) {
    $popplerDirectories = glob(
        rtrim($localAppData, '\\/').DIRECTORY_SEPARATOR.
            'Microsoft'.DIRECTORY_SEPARATOR.
            'WinGet'.DIRECTORY_SEPARATOR.
            'Packages'.DIRECTORY_SEPARATOR.
            'oschwartz10612.Poppler_*'.DIRECTORY_SEPARATOR.
            'poppler-*'.DIRECTORY_SEPARATOR.
            'Library'.DIRECTORY_SEPARATOR.
            'bin',
        GLOB_ONLYDIR,
    ) ?: [];

    rsort($popplerDirectories, SORT_NATURAL);

    foreach ($popplerDirectories as $directory) {
        if (is_file($directory.DIRECTORY_SEPARATOR.'pdfinfo.exe')
            && is_file($directory.DIRECTORY_SEPARATOR.'pdftoppm.exe')) {
            $popplerBinDirectory = $directory;
            break;
        }
    }
}

$pdfInfoBinary = $popplerBinDirectory
    ? $popplerBinDirectory.DIRECTORY_SEPARATOR.'pdfinfo.exe'
    : 'pdfinfo';
$pdfToPpmBinary = $popplerBinDirectory
    ? $popplerBinDirectory.DIRECTORY_SEPARATOR.'pdftoppm.exe'
    : 'pdftoppm';
$tesseractBinary = PHP_OS_FAMILY === 'Windows'
    && is_file('C:\\Program Files\\Tesseract-OCR\\tesseract.exe')
        ? 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe'
        : 'tesseract';

return [
    /*
     * On Windows, discover the Winget Poppler install and standard Tesseract
     * install automatically. Environment variables override these defaults.
     */
    'pdfinfo_binary' => env('PDFINFO_BINARY', $pdfInfoBinary),
    'pdftoppm_binary' => env('PDFTOPPM_BINARY', $pdfToPpmBinary),
    'tesseract_binary' => env('TESSERACT_BINARY', $tesseractBinary),
    'ocr_language' => env('PDF_OCR_LANGUAGE', 'eng'),
    'max_ocr_pages' => (int) env('PDF_OCR_MAX_PAGES', 100),
];
