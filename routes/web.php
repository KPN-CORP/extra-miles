<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\NotificationMiddleware;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\QuotesController;
use App\Http\Controllers\EventParticipantController;
use App\Livewire\ManageParticipants;

Route::get('language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');

Route::get('dbauth', [SsoController::class, 'dbauth']);

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
                ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
    ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.store');
                
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
                    ->name('password.request');
                    
    Route::get('reset-password-email', [PasswordResetLinkController::class, 'selfResetView']);
    
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                    ->name('password.email');    
});


Route::middleware('auth', 'locale', 'notification')->group(function () {

    Route::get('/', function () {
        return redirect('/admin/dashboard');
    });

    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // News
    Route::get('/admin/news', [NewsController::class, 'index'])->name('admin.news.index');

    // Event
    Route::get('/admin/events', [EventController::class, 'index'])->name('admin.events.index');
    Route::get('/admin/events/create', [EventController::class, 'create'])->name('admin.events.create');
    Route::post('/admin/events/store', [EventController::class, 'store'])->name('events.store');
    Route::delete('/admin/events/{id}/archive', [EventController::class, 'softDelete'])->name('events.softDelete');
    Route::post('/admin/events/{id}/close', [EventController::class, 'closeRegistration'])->name('events.close');
    Route::post('/admin/events/{id}/toggle-status', [EventController::class, 'toggleStatus'])->name('events.close');
    Route::get('/admin/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/admin/events/{id}', [EventController::class, 'update'])->name('events.update');
    Route::get('/admin/events/{encryptedId}/participants', [EventParticipantController::class, 'listParticipants'])->name('events.participants');
    Route::post('/admin/participants/{id}/approve', [EventParticipantController::class, 'approve'])->name('participants.approve');
    Route::post('/admin/participants/{id}/reject', [EventParticipantController::class, 'reject'])->name('participants.reject');
    Route::get('/ticket/qr-png/{encryptedId}', [EventController::class, 'showQRPNG'])->name('event.qrpng');
    Route::get('/participants/export/{event_id}', [EventParticipantController::class, 'export'])->name('participants.export');

    // Survey
    Route::get('/admin/survey', [SurveyController::class, 'index'])->name('admin.survey.index');
    Route::get('/admin/survey/create', [SurveyController::class, 'create'])->name('admin.survey.create');
    Route::post('/admin/survey/store', [SurveyController::class, 'store'])->name('survey.store');
    Route::get('/admin/survey/{id}/edit', [SurveyController::class, 'edit'])->name('survey.edit');
    Route::put('/admin/survey/{id}', [SurveyController::class, 'update'])->name('survey.update');
    Route::post('/admin/survey/{id}/archive', [SurveyController::class, 'archive'])->name('survey.archive');
    Route::get('/admin/survey/{encryptedId}/participants', [SurveyController::class, 'listParticipants'])->name('survey.participants');

    // Social
    Route::get('/admin/social', [SocialController::class, 'index'])->name('admin.social.index');

    // Quotes
    Route::get('/admin/quotes', [QuotesController::class, 'index'])->name('admin.quotes.index');
    
    // Authentication
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('{first}/{second}', [HomeController::class, 'secondLevel'])->name('second');
    Route::get('reset-self', [PasswordResetLinkController::class, 'selfReset'])
        ->name('password.reset.self');
});

Route::fallback(function () {
    return view('errors.404');
});

require __DIR__.'/auth.php';