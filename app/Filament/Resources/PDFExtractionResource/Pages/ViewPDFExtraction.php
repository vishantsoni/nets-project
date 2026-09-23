<?php
namespace App\Filament\Resources\PDFExtractionResource\Pages;

use App\Filament\Resources\PDFExtractionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPDFExtraction extends ViewRecord
{
    protected static string $resource = PDFExtractionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}