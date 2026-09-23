<?php

namespace App\Filament\Resources\B2BEnquiryResource\Pages;

use App\Filament\Resources\B2BEnquiryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditB2BEnquiry extends EditRecord
{
    protected static string $resource = B2BEnquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
