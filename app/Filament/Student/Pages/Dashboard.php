<?php

namespace App\Filament\Student\Pages;

use App\Models\ExamAttempt;
use App\Models\Examination;
use App\Models\Result;
use App\Models\StudyMaterial;
use Filament\Pages\Page;
use Filament\Widgets\AccountWidget;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $title = 'Dashboard';
    protected static string $view = 'filament.student.pages.dashboard';
    protected ?string $heading = 'Student Dashboard';

    public function getStats(): array
    {
        $user = auth()->user();

        $availableExams = Examination::where('is_active', true)
            ->where('is_published', true)
            ->where(function ($q) use ($user) {
                $q->whereNull('start_time')->orWhere('start_time', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_time')->orWhere('end_time', '>=', now());
            })
            ->count();

        $takenExams = ExamAttempt::where('user_id', $user->id)->distinct('examination_id')->count('examination_id');

        $pendingResults = Result::whereHas('attempt', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereHas('attempt', function ($q) {
            $q->where('status', 'completed');
        })->count();

        $purchasedMaterials = StudyMaterial::where('is_paid', true)
            ->whereHas('orderItems', function ($q) use ($user) {
                $q->where('orders.user_id', $user->id)
                    ->where('orders.payment_status', 'paid');
            })
            ->count();

        return [
            'availableExams' => $availableExams,
            'takenExams' => $takenExams,
            'pendingResults' => $pendingResults,
            'purchasedMaterials' => $purchasedMaterials,
        ];
    }

    public function getAvailableExams()
    {
        $user = auth()->user();

        return Examination::where('is_active', true)
            ->where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('start_time')->orWhere('start_time', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_time')->orWhere('end_time', '>=', now());
            })
            ->with('subject')
            ->limit(5)
            ->get();
    }

    public function getRecentResults()
    {
        $user = auth()->user();

        return Result::whereHas('attempt', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->with(['attempt.examination'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function getRecentMaterials()
    {
        $user = auth()->user();

        return StudyMaterial::where('is_published', true)
            ->with('subject')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }
}
