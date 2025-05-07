<?php

use App\Http\Controllers\Admin\AppraisalController as AdminAppraisalController;
use App\Http\Controllers\Admin\OnBehalfController as AdminOnBehalfController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SendbackController as AdminSendbackController;
use App\Http\Controllers\AdminImportController;
use App\Http\Controllers\Appraisal360;
use App\Http\Controllers\AppraisalTaskController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\ImportGoalsController;
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
use App\Http\Controllers\ExportExcelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LayerController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SendbackController;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\MyAppraisalController;
use App\Http\Controllers\MyGoalController;
use App\Http\Controllers\RatingAdminController;
use App\Http\Controllers\CalibrationController;
use App\Http\Controllers\EmployeePAController;
use App\Http\Controllers\FormAppraisalController;
use App\Http\Controllers\FormGroupAppraisalController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\TeamAppraisalController;
use App\Http\Controllers\TeamGoalController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\WeightageController;
use App\Imports\ApprovalLayerAppraisalImport;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\NotificationMiddleware;
use App\Http\Controllers\EventController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\QuotesController;

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
        return redirect('dashboard');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');

    Route::get('/events', [EventController::class, 'index'])->name('events.index');
    Route::get('/events/create', [EventController::class, 'create'])->name('events.create');

    Route::get('/survey', [SurveyController::class, 'index'])->name('survey.index');

    Route::get('/social', [SocialController::class, 'index'])->name('social.index');

    Route::get('/quotes', [QuotesController::class, 'index'])->name('quotes.index');
    
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