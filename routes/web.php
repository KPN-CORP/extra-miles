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
use App\Http\Controllers\LiveContentController;
use App\Http\Controllers\QuotesController;
use App\Http\Controllers\EventParticipantController;
use App\Http\Controllers\FormTemplateController;
use App\Livewire\ManageParticipants;


Route::get('dbauth', [SsoController::class, 'dbauth']);

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
                ->name('register');
});

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

        // News
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
        
        // News
        Route::get('/news', [NewsController::class, 'index'])->name('admin.news.index');
        Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
        Route::post('/news/store', [NewsController::class, 'store'])->name('news.store');
        Route::get('/news/edit/{id}', [NewsController::class, 'edit'])->name('news.edit');
        Route::put('/news/{id}', [NewsController::class, 'update'])->name('news.update');
        Route::delete('/news/{id}/archive', [NewsController::class, 'archive'])->name('news.archive');

        // Event
        Route::get('/events', [EventController::class, 'index'])->name('admin.events.index');
        Route::get('/events/create', [EventController::class, 'create'])->name('admin.events.create');
        Route::post('/events/store', [EventController::class, 'store'])->name('events.store');
        Route::delete('/events/{id}/archive', [EventController::class, 'softDelete'])->name('events.softDelete');
        Route::post('/events/{id}/close', [EventController::class, 'closeRegistration'])->name('events.close');
        Route::post('/events/{id}/toggle-status', [EventController::class, 'toggleStatus'])->name('events.close');
        Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
        Route::get('/events/{encryptedId}/participants', [EventParticipantController::class, 'listParticipants'])->name('events.participants');
        Route::post('/participants/{id}/approve', [EventParticipantController::class, 'approve'])->name('participants.approve');
        Route::post('/participants/{id}/reject', [EventParticipantController::class, 'reject'])->name('participants.reject');
        Route::get('/ticket/qr-png/{encryptedId}', [EventController::class, 'showQRPNG'])->name('event.qrpng');
        Route::get('/participants/export/{event_id}', [EventParticipantController::class, 'export'])->name('participants.export');
        Route::post('/participants/bulk-approve', [EventParticipantController::class, 'bulkApprove'])->name('participants.bulkApprove');

        // Survey
        Route::get('/survey', [SurveyController::class, 'index'])->name('admin.survey.index');
        Route::get('/survey/create', [SurveyController::class, 'create'])->name('admin.survey.create');
        Route::post('/survey/store', [SurveyController::class, 'store'])->name('survey.store');
        Route::get('/survey/{id}/edit', [SurveyController::class, 'edit'])->name('survey.edit');
        Route::put('/survey/{id}', [SurveyController::class, 'update'])->name('survey.update');
        Route::post('/survey/{id}/archive', [SurveyController::class, 'archive'])->name('survey.archive');
        Route::get('/survey/{encryptedId}/participants', [SurveyController::class, 'listParticipants'])->name('survey.participants');
        Route::get('/vote/{encryptedId}/participants', [SurveyController::class, 'listVoteParticipants'])->name('vote.participants');
        Route::get('/survey/{survey_id}/export', [SurveyController::class, 'export'])->name('survey.export');

        // Social
        Route::get('/social', [SocialController::class, 'index'])->name('admin.social.index');
        Route::post('/social/store', [SocialController::class, 'store'])->name('social.store');
        Route::delete('/social/{id}/delete', [SocialController::class, 'destroy'])->name('social.destroy');
        Route::put('/social/{id}', [SocialController::class, 'update'])->name('social.update');

        //Live
        Route::get('/live', [LiveContentController::class, 'index'])->name('live.index');
        Route::post('/live/store', [LiveContentController::class, 'store'])->name('live.store');
        Route::delete('/live/{id}', [LiveContentController::class, 'destroy'])->name('live.destroy');

        // Quotes
        Route::get('/quotes', [QuotesController::class, 'index'])->name('admin.quotes.index');
        Route::post('/quotes/store', [QuotesController::class, 'store'])->name('quotes.store');
        Route::put('/quotes/{id}', [QuotesController::class, 'update'])->name('quotes.update');
        Route::delete('/quotes/{id}/delete', [QuotesController::class, 'destroy'])->name('quotes.destroy');

        // Form Builder
        Route::get('/formbuilder', [FormTemplateController::class, 'index'])->name('form.index');
        Route::get('/formbuilder/create', [FormTemplateController::class, 'create'])->name('form.create');
        Route::post('/form-builder/store', [FormTemplateController::class, 'store'])->name('form-builder.store');
        Route::delete('/formbuilder/archive/{id}', [FormTemplateController::class, 'archive'])->name('formbuilder.archive');
        Route::get('/formbuilder/{id}/edit', [FormTemplateController::class, 'edit'])->name('formbuilder.edit');
        Route::put('/formbuilder/{id}', [FormTemplateController::class, 'update'])->name('formbuilder.update');
        Route::get('/forms/{id}/schema', [FormTemplateController::class, 'getSchema']);

        Route::get('{first}/{second}', [HomeController::class, 'secondLevel'])->name('second');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
        Route::get('login', function () {
            return redirect()->away('https://kpncorporation.darwinbox.com');
        })->name('login');
    });
    

    Route::fallback(function () {
        return view('errors.404');
    });
    
    require __DIR__.'/auth.php';
});

