<?php

namespace App\Filament\Student\Resources\ResultResource\RelationManagers;

use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StudentAnswerRelationManager extends RelationManager
{
    protected static string $relationship = 'answers';

    protected static ?string $recordLabel = 'Answer';

    protected static ?string $label = 'Question Review';

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\ViewColumn::make('question.question_text')
                ->label('Question')
                ->view('filament.student.tables.math-content'),
            Tables\Columns\TextColumn::make('question.question_type')
                ->label('Type')
                ->badge()
                ->colors([
                    'primary' => 'mcq',
                    'success' => 'true_false',
                    'warning' => 'integer',
                    'info' => 'fill_blank',
                    'danger' => 'essay',
                ]),
            Tables\Columns\ViewColumn::make('student_answer')
                ->label('Your Answer')
                ->view('filament.student.tables.math-content'),
            Tables\Columns\ViewColumn::make('correct_answer')
                ->label('Correct Answer')
                ->view('filament.student.tables.math-content')
                ->getStateUsing(function ($record) {
                    return $record->correct_answer_display;
                }),
            Tables\Columns\IconColumn::make('is_correct')
                ->label('Status')
                ->boolean(),
            Tables\Columns\TextColumn::make('marks_obtained')
                ->label('Marks')
                ->numeric(2),
        ])->defaultSort('question_id')
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
