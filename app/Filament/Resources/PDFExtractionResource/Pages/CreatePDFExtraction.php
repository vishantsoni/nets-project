<?php

namespace App\Filament\Resources\PDFExtractionResource\Pages;

use App\Filament\Resources\PDFExtractionResource;
use App\Models\PDFExtraction;
use App\Services\PdfExtraction\McqQuestionParser;
use App\Services\PdfExtraction\PdfContentExtractor;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreatePDFExtraction extends CreateRecord
{
    protected static string $resource = PDFExtractionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = Auth::id();
        $data['status'] = 'pending';
        $data['parsed_questions'] = [];

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var PDFExtraction $record */
        $record = $this->getRecord();
        $record->update(['status' => 'processing', 'error_message' => null]);

        try {
            $content = app(PdfContentExtractor::class)->extract($record->file_path);
            $questions = app(McqQuestionParser::class)->parse($content);

            $record->update([
                'extracted_content' => $content,
                'parsed_questions' => $questions,
                'status' => 'completed',
                'error_message' => $questions === []
                    ? 'The PDF text was read, but no MCQ questions with labeled options were recognized. Review the extracted text and use the Question Bank to add questions manually.'
                    : null,
            ]);

            if ($questions === []) {
                Notification::make()
                    ->warning()
                    ->title('PDF text extracted, but no questions were recognized')
                    ->body('Review the extracted content, then add or correct questions before importing.')
                    ->send();

                return;
            }

            Notification::make()
                ->success()
                ->title('PDF questions are ready to review')
                ->body(count($questions).' question(s) detected. Review and select the questions to import.')
                ->send();
        } catch (Throwable $exception) {
            Log::error('PDF question extraction failed.', [
                'pdf_extraction_id' => $record->getKey(),
                'exception' => $exception,
            ]);

            $record->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Could not read the PDF')
                ->body($exception->getMessage())
                ->send();
        }
    }

    protected function getCreatedNotification(): ?Notification
    {
        return null;
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}
