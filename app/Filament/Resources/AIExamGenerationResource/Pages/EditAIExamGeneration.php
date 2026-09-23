<?php

namespace App\Filament\Resources\AIExamGenerationResource\Pages;

use App\Filament\Resources\AIExamGenerationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAIExamGeneration extends EditRecord
{
    protected static string $resource = AIExamGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
