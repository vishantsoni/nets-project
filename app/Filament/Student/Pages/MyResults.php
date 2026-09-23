<?php

namespace App\Filament\Student\Pages;

use App\Models\ExamAttempt;
use Filament\Pages\Page;

class MyResults extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-academic-badge';
    protected static ?string $title = 'My Results';
    protected static string $view = 'filament.student.pages.my-results';
    protected static ?string $navigationGroup = 'Examinations';
    protected static ?int $navigationSort = 2;

    public function getAttempts()
    {
        $user = auth()->user();

        return ExamAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with(['examination', 'result'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
