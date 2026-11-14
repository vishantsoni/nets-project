<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AIExamGenerationResource\Pages;
use App\Filament\Resources\Concerns\ScopesToInstitute;
use App\Models\AIExamGeneration;
use App\Models\Topic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AIExamGenerationResource extends Resource
{
    use ScopesToInstitute;

    protected static ?string $model = AIExamGeneration::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Question Bank';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('AI Generation')
                ->schema([
                    Forms\Components\Select::make('subject_id')
                        ->required()
                        ->relationship(
                            'subject',
                            'name',
                            modifyQueryUsing: fn (Builder $query) => $query->forInstitute(auth()->user())
                        )
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (Set $set): void {
                            $set('chapter_id', null);
                            $set('topic_id', null);
                            $set('examination_id', null);
                        }),
                    Forms\Components\Select::make('chapter_id')
                        ->label('Chapter (optional)')
                        ->relationship(
                            'chapter',
                            'name',
                            modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                ->forInstitute(auth()->user())
                                ->where('subject_id', $get('subject_id'))
                        )
                        ->disabled(fn (Get $get): bool => blank($get('subject_id')))
                        ->helperText('Optional generation context. Selecting a chapter filters the topic list.')
                        ->searchable()
                        ->nullable()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('topic_id', null)),
                    Forms\Components\Select::make('topic_id')
                        ->relationship(
                            'topic',
                            'name',
                            modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                ->forInstitute(auth()->user())
                                ->whereHas(
                                    'chapter',
                                    fn (Builder $query) => $query
                                        ->where('subject_id', $get('subject_id'))
                                        ->when(
                                            filled($get('chapter_id')),
                                            fn (Builder $query) => $query->whereKey($get('chapter_id'))
                                        )
                                )
                        )
                        ->getOptionLabelFromRecordUsing(fn (Topic $record): string => $record->chapter->name.' / '.$record->name)
                        ->disabled(fn (Get $get): bool => blank($get('subject_id')))
                        ->searchable(),
                    Forms\Components\Select::make('examination_id')
                        ->label('Target exam / test (optional)')
                        ->relationship(
                            'examination',
                            'title',
                            modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                ->forInstitute(auth()->user())
                                ->where('subject_id', $get('subject_id'))
                        )
                        ->disabled(fn (Get $get): bool => blank($get('subject_id')))
                        ->searchable()
                        ->nullable(),
                    Forms\Components\Hidden::make('question_type')->default('mcq'),
                    Forms\Components\TextInput::make('count')->integer()->required()->minValue(1)->maxValue(50)->default(10),
                    Forms\Components\Select::make('difficulty')
                        ->options(['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard'])->default('medium'),
                    Forms\Components\Select::make('language')
                        ->options(['English' => 'English', 'Hindi' => 'Hindi', 'Bilingual' => 'Bilingual'])
                        ->required()
                        ->default('English'),
                    Forms\Components\Textarea::make('prompt')
                        ->label('Topic / Syllabus')
                        ->required()
                        ->rows(4)
                        ->helperText('Describe the topic or syllabus for the questions. A selected chapter is added as optional context.'),
                    Forms\Components\Select::make('status')
                        ->options(['pending' => 'Pending', 'generating' => 'Generating', 'completed' => 'Completed', 'failed' => 'Failed'])
                        ->default('pending')
                        ->disabled()
                        ->dehydrated(false),
                    Forms\Components\Repeater::make('generated_questions')
                        ->label('Review and edit generated questions')
                        ->defaultItems(0)
                        ->itemLabel(fn (array $state): ?string => filled($state['question_text'] ?? null)
                            ? Str::limit($state['question_text'], 70)
                            : 'Question')
                        ->schema([
                            Forms\Components\Textarea::make('question_text')
                                ->required()
                                ->rows(3)
                                ->extraInputAttributes(['data-math-input' => true])
                                ->columnSpanFull(),
                            Forms\Components\Textarea::make('option_a')->required()->label('Option A')->rows(2)
                                ->extraInputAttributes(['data-math-input' => true]),
                            Forms\Components\Textarea::make('option_b')->required()->label('Option B')->rows(2)
                                ->extraInputAttributes(['data-math-input' => true]),
                            Forms\Components\Textarea::make('option_c')->required()->label('Option C')->rows(2)
                                ->extraInputAttributes(['data-math-input' => true]),
                            Forms\Components\Textarea::make('option_d')->required()->label('Option D')->rows(2)
                                ->extraInputAttributes(['data-math-input' => true]),
                            Forms\Components\Select::make('correct_option')
                                ->required()
                                ->options(['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D'])
                                ->label('Correct option'),
                            Forms\Components\Toggle::make('selected')
                                ->label('Include in bulk import')
                                ->default(true),
                            Forms\Components\Textarea::make('explanation')
                                ->rows(2)
                                ->extraInputAttributes(['data-math-input' => true]),
                            Forms\Components\Hidden::make('imported_question_id'),
                        ])
                        ->columns(2)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('error_message')->rows(2)->readOnly(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('subject.name')->label('Subject'),
            Tables\Columns\BadgeColumn::make('question_type'),
            Tables\Columns\TextColumn::make('count'),
            Tables\Columns\TextColumn::make('difficulty'),
            Tables\Columns\BadgeColumn::make('status')
                ->colors(['gray' => 'pending', 'warning' => 'generating', 'success' => 'completed', 'danger' => 'failed']),
            Tables\Columns\TextColumn::make('created_at')->dateTime(),
        ])->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(['pending' => 'Pending', 'generating' => 'Generating', 'completed' => 'Completed', 'failed' => 'Failed']),
                Tables\Filters\SelectFilter::make('subject_id')->relationship('subject', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAIExamGenerations::route('/'),
            'create' => Pages\CreateAIExamGeneration::route('/create'),
            'view' => Pages\ViewAIExamGeneration::route('/{record}'),
            'edit' => Pages\EditAIExamGeneration::route('/{record}/edit'),
        ];
    }
}
