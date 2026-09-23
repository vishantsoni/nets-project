<?php

namespace App\Filament\Resources\OMRSheetResource\Pages;

use App\Filament\Resources\OMRSheetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOMRSheets extends ListRecords
{
    protected static string $resource = OMRSheetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
