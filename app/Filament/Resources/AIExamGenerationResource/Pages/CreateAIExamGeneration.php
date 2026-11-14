<?php

namespace App\Filament\Resources\AIExamGenerationResource\Pages;

use App\Filament\Resources\AIExamGenerationResource;
use App\Models\AIExamGeneration;
use App\Services\QuestionAiService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateAIExamGeneration extends CreateRecord
{
    protected static string $resource = AIExamGenerationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        $data['status'] = 'generating';
        $data['generated_questions'] = [];

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var AIExamGeneration $record */
        $record = $this->getRecord();

        try {
            $questions = app(QuestionAiService::class)->generateQuestions(
                $record->prompt,
                $record->difficulty,
                $record->count,
                $record->language,
                $record->chapter?->name,
            );
            $questions = array_map(
                fn (array $question): array => [
                    ...$question,
                    'selected' => true,
                    'imported_question_id' => null,
                ],
                $questions,
            );

            $record->update([
                'generated_questions' => $questions,
                'status' => 'completed',
                'error_message' => null,
            ]);

            Notification::make()
                ->success()
                ->title('AI questions are ready to review')
                ->body(count($questions).' question(s) generated. Review and edit them before importing.')
                ->send();
        } catch (Throwable $exception) {
            Log::error('AI question generation failed.', [
                'ai_generation_id' => $record->getKey(),
                'exception' => $exception,
            ]);

            $record->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

            Notification::make()
                ->danger()
                ->title('Could not generate questions')
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
