<?php
namespace App\Filament\Resources\AIExamGenerationResource\Pages;

use App\Filament\Resources\AIExamGenerationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAIExamGeneration extends ViewRecord
{
    protected static string $resource = AIExamGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}