<?php
namespace App\Filament\Resources\B2BEnquiryResource\Pages;

use App\Filament\Resources\B2BEnquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewB2BEnquiry extends ViewRecord
{
    protected static string $resource = B2BEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}