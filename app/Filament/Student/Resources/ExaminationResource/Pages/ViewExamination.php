<?php
namespace App\Filament\Student\Resources\ExaminationResource\Pages;

use App\Filament\Student\Resources\ExaminationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExamination extends ViewRecord
{
    protected static string $resource = ExaminationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make("take_exam")
                ->label("Take Exam")
                ->color("success")
                ->icon("heroicon-o-play")
                ->url(fn ($record) => route("student.exams.take", $record)),
        ];
    }
}
