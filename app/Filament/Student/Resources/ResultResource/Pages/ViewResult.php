<?php
namespace App\Filament\Student\Resources\ResultResource\Pages;

use App\Filament\Student\Resources\ResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewResult extends ViewRecord
{
    protected static string $resource = ResultResource::class;

    protected function getHeaderActions(): array
    {
        $examinationId = $this->getRecord()->attempt?->examination_id;

        return $examinationId
            ? [
                Actions\Action::make('leaderboard')
                    ->label('Live Leaderboard')
                    ->icon('heroicon-o-trophy')
                    ->url(route('filament.student.student.exams.leaderboard', ['examination' => $examinationId])),
            ]
            : [];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $attempt = $this->getRecord()->attempt;

        $data["attempt"] = $attempt
            ? ["examination" => ["title" => $attempt->examination?->title]]
            : null;

        return $data;
    }
}
