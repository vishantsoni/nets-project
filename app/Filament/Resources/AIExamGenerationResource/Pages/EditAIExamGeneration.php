<?php

namespace App\Filament\Resources\AIExamGenerationResource\Pages;

use App\Filament\Resources\AIExamGenerationResource;
use App\Services\QuestionImportService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditAIExamGeneration extends EditRecord
{
    protected static string $resource = AIExamGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importSelectedQuestions')
                ->label('Approve & Save Questions')
                ->icon('heroicon-o-arrow-down-tray')
                ->modalDescription('Questions assigned to a target exam are published when saved. Students may see them immediately if that exam is already active.')
                ->requiresConfirmation()
                ->visible(fn (): bool => $this->record->status === 'completed')
                ->action(fn () => $this->importSelectedQuestions()),
            Actions\DeleteAction::make(),
        ];
    }

    protected function importSelectedQuestions(): void
    {
        $data = $this->form->getState();
        $generatedQuestions = $data['generated_questions'] ?? [];
        $selectedQuestions = array_filter(
            $generatedQuestions,
            fn (array $question): bool => (bool) ($question['selected'] ?? false)
                && blank($question['imported_question_id'] ?? null),
        );

        if ($selectedQuestions === []) {
            Notification::make()
                ->warning()
                ->title('No questions selected')
                ->body('Select at least one question that has not already been imported.')
                ->send();

            return;
        }

        $result = DB::transaction(function () use ($data, $generatedQuestions, $selectedQuestions): array {
            $this->record->fill([
                'subject_id' => $data['subject_id'],
                'chapter_id' => $data['chapter_id'] ?? null,
                'topic_id' => $data['topic_id'] ?? null,
                'examination_id' => $data['examination_id'] ?? null,
                'generated_questions' => $generatedQuestions,
            ])->save();

            $result = app(QuestionImportService::class)->import(
                $selectedQuestions,
                (int) $this->record->subject_id,
                filled($this->record->topic_id) ? (int) $this->record->topic_id : null,
                filled($this->record->examination_id) ? (int) $this->record->examination_id : null,
                $this->record->difficulty,
                true,
            );

            foreach ($result['processed'] as $index => $questionId) {
                $generatedQuestions[$index]['selected'] = false;
                $generatedQuestions[$index]['imported_question_id'] = $questionId;
            }

            $this->record->update(['generated_questions' => $generatedQuestions]);

            return $result;
        });

        $this->record->refresh();
        $this->form->fill($this->record->toArray());

        $notification = Notification::make()
            ->title($result['saved'] > 0 ? 'Questions imported' : 'No new questions imported')
            ->body("{$result['saved']} question(s) saved; {$result['duplicates']} duplicate(s) skipped.");

        if ($result['saved'] > 0) {
            $notification->success();
        } else {
            $notification->warning();
        }

        $notification->send();
    }
}
