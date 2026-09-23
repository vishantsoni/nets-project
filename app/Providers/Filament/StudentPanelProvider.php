<?php

namespace App\Providers\Filament;

use App\Filament\Student\Pages\Dashboard as StudentDashboard;
use App\Filament\Student\Pages\MyMaterials;
use App\Filament\Student\Pages\MyOrders;
use App\Filament\Student\Pages\MyResults;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class StudentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('student')
            ->path('student')
            ->login()
            ->registration()
            ->profile()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Student/Resources'), for: 'App\\Filament\\Student\\Resources')
            ->discoverPages(in: app_path('Filament/Student/Pages'), for: 'App\\Filament\\Student\\Pages')
            ->pages([
                StudentDashboard::class,
                MyResults::class,
                MyMaterials::class,
                MyOrders::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Student/Widgets'), for: 'App\\Filament\\Student\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->routes(function (Panel $panel) {
                Route::post("exams/save-answer", [\App\Http\Controllers\StudentExamController::class, "saveAnswer"])
                    ->middleware(\App\Http\Middleware\StudentMiddleware::class)
                    ->name("student.exams.save-answer");

                Route::post("exams/submit", [\App\Http\Controllers\StudentExamController::class, "submit"])
                    ->middleware(\App\Http\Middleware\StudentMiddleware::class)
                    ->name("student.exams.submit");
            });
    }
}
