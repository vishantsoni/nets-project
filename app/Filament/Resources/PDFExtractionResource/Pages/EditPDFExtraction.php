<?php

namespace App\Filament\Resources\PDFExtractionResource\Pages;

use App\Filament\Resources\PDFExtractionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPDFExtraction extends EditRecord
{
    protected static string $resource = PDFExtractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
