<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Middleware\NotificationMiddleware;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\QuotesController;
use App\Http\Controllers\EventParticipantController;
use App\Livewire\ManageParticipants;

Route::get('/{any?}', function () {
    return view('user-app');
})->where('any', '^(?!admin).*$');

Route::get('/images/{filename}', function ($filename) {
    $path = storage_path('app/public/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
});

Route::prefix('admin')->group(function () {
    
    Route::middleware('auth', 'locale', 'notification')->group(function () {

        Route::get('/admin/news', [NewsController::class, 'index'])->name('admin.news.index');
    
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
    
        Route::get('/admin/survey', [SurveyController::class, 'index'])->name('admin.survey.index');
        Route::get('/admin/survey/create', [SurveyController::class, 'create'])->name('admin.survey.create');
    
        Route::get('/admin/social', [SocialController::class, 'index'])->name('admin.social.index');
    
        Route::get('/admin/quotes', [QuotesController::class, 'index'])->name('admin.quotes.index');
    });
    
    Route::fallback(function () {
        return view('errors.404');
    });
    
    require __DIR__.'/auth.php';
});

