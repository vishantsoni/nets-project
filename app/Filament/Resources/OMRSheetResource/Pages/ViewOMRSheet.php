<?php
namespace App\Filament\Resources\OMRSheetResource\Pages;

use App\Filament\Resources\OMRSheetResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOMRSheet extends ViewRecord
{
    protected static string $resource = OMRSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}