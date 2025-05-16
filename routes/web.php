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

Route::get('language/{locale}', [LanguageController::class, 'switchLanguage'])->name('language.switch');

Route::get('dbauth', [SsoController::class, 'dbauth']);
Route::get('sourcermb/dbauth', [SsoController::class, 'dbauthReimburse']);

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
    
    Route::get('/admin/news', [NewsController::class, 'index'])->name('admin.news.index');

    Route::get('/admin/events', [EventController::class, 'index'])->name('admin.events.index');
    Route::get('/admin/events/create', [EventController::class, 'create'])->name('admin.events.create');
    Route::post('/events/store', [EventController::class, 'store'])->name('events.store');
    Route::delete('/events/{id}/archive', [EventController::class, 'softDelete'])->name('events.softDelete');
    Route::post('/events/{id}/close', [EventController::class, 'closeRegistration'])->name('events.close');
    Route::post('/events/{id}/toggle-status', [EventController::class, 'toggleStatus'])->name('events.close');
    Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
    Route::get('/events/{encryptedId}/participants', [EventController::class, 'listParticipants'])->name('events.participants');
    Route::post('/participants/{id}/approve', [EventParticipantController::class, 'approve'])->name('participants.approve');
    Route::post('/participants/{id}/reject', [EventParticipantController::class, 'reject'])->name('participants.reject');
    Route::post('/participants/{id}/reinvite', [EventParticipantController::class, 'reinvite'])->name('participants.reinvite');
    

    Route::get('/admin/survey', [SurveyController::class, 'index'])->name('admin.survey.index');

    Route::get('/admin/social', [SocialController::class, 'index'])->name('admin.social.index');

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