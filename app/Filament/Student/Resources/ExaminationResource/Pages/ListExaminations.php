<?php
namespace App\Filament\Student\Resources\ExaminationResource\Pages;

use App\Filament\Student\Resources\ExaminationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExaminations extends ListRecords
{
    protected static string $resource = ExaminationResource::class;
}
