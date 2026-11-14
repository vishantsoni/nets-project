<?php

namespace App\Filament\Resources\PDFExtractionResource\Pages;

use App\Filament\Resources\PDFExtractionResource;
use App\Services\QuestionImportService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditPDFExtraction extends EditRecord
{
    protected static string $resource = PDFExtractionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['parsed_questions'] = array_map(function (array $question): array {
            if (array_key_exists('option_a', $question)) {
                return $question;
            }

            $options = collect($question['options'] ?? [])
                ->keyBy(fn (array $option): string => strtoupper((string) ($option['option_value'] ?? '')));
            $correctOption = $options
                ->first(fn (array $option): bool => (bool) ($option['is_correct'] ?? false));

            foreach (['A', 'B', 'C', 'D'] as $option) {
                $question['option_'.strtolower($option)] = $options->get($option)['option_text'] ?? '';
            }

            $question['correct_option'] = strtoupper((string) ($correctOption['option_value'] ?? ''));

            return $question;
        }, $data['parsed_questions'] ?? []);

        return $data;
    }

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
        $parsedQuestions = $data['parsed_questions'] ?? [];
        $selectedQuestions = array_filter(
            $parsedQuestions,
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

        $result = DB::transaction(function () use ($data, $parsedQuestions, $selectedQuestions): array {
            $this->record->fill([
                'subject_id' => $data['subject_id'],
                'topic_id' => $data['topic_id'] ?? null,
                'examination_id' => $data['examination_id'] ?? null,
                'parsed_questions' => $parsedQuestions,
            ])->save();

            $result = app(QuestionImportService::class)->import(
                $selectedQuestions,
                (int) $this->record->subject_id,
                filled($this->record->topic_id) ? (int) $this->record->topic_id : null,
                filled($this->record->examination_id) ? (int) $this->record->examination_id : null,
                'medium',
                false,
            );

            foreach ($result['processed'] as $index => $questionId) {
                $parsedQuestions[$index]['selected'] = false;
                $parsedQuestions[$index]['imported_question_id'] = $questionId;
            }

            $this->record->update(['parsed_questions' => $parsedQuestions]);

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
