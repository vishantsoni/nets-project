<?php
namespace App\Filament\Student\Resources\CategoryResource\Pages;

use App\Filament\Student\Resources\CategoryResource;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;
}
