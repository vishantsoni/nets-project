<?php

namespace App\Filament\Resources\PDFExtractionResource\Pages;

use App\Filament\Resources\PDFExtractionResource;
use App\Models\Question;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditPDFExtraction extends EditRecord
{
    protected static string $resource = PDFExtractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importSelectedQuestions')
                ->label('Import selected questions')
                ->icon('heroicon-o-arrow-down-tray')
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

        foreach ($selectedQuestions as $index => $question) {
            $correctOptions = array_filter(
                $question['options'] ?? [],
                fn (array $option): bool => (bool) ($option['is_correct'] ?? false),
            );

            if (blank($question['question_text'] ?? null) || count($correctOptions) !== 1) {
                throw ValidationException::withMessages([
                    "data.parsed_questions.{$index}.options" => 'Each selected question must have question text and exactly one correct option.',
                ]);
            }
        }

        $this->record->fill([
            'subject_id' => $data['subject_id'],
            'topic_id' => $data['topic_id'] ?? null,
            'parsed_questions' => $parsedQuestions,
        ])->save();

        $importedCount = DB::transaction(function (): int {
            $parsedQuestions = $this->record->parsed_questions ?? [];
            $importedCount = 0;

            foreach ($parsedQuestions as $index => $questionData) {
                if (! ($questionData['selected'] ?? false) || filled($questionData['imported_question_id'] ?? null)) {
                    continue;
                }

                $question = Question::create([
                    'question_text' => trim($questionData['question_text']),
                    'question_type' => 'mcq',
                    'subject_id' => $this->record->subject_id,
                    'topic_id' => $this->record->topic_id,
                    'difficulty' => 'medium',
                    'explanation' => filled($questionData['explanation'] ?? null)
                        ? trim($questionData['explanation'])
                        : null,
                    'status' => 'draft',
                    'is_ai_generated' => false,
                    'institute_id' => $this->record->subject?->institute_id ?? Auth::user()?->institute_id,
                    'created_by' => Auth::id(),
                ]);

                foreach (array_values($questionData['options']) as $sortOrder => $optionData) {
                    $question->options()->create([
                        'option_text' => trim($optionData['option_text']),
                        'option_value' => strtoupper(trim($optionData['option_value'])),
                        'is_correct' => (bool) ($optionData['is_correct'] ?? false),
                        'sort_order' => $sortOrder,
                    ]);
                }

                $parsedQuestions[$index]['selected'] = false;
                $parsedQuestions[$index]['imported_question_id'] = $question->getKey();
                $importedCount++;
            }

            $this->record->update(['parsed_questions' => $parsedQuestions]);

            return $importedCount;
        });

        $this->record->refresh();
        $this->form->fill($this->record->toArray());

        Notification::make()
            ->success()
            ->title('Questions imported')
            ->body("{$importedCount} question(s) were added to the Question Bank as drafts.")
            ->send();
    }
}
