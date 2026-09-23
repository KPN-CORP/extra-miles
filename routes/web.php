<?php

use App\Http\Controllers\AdminFallbackController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\DevLoginController;
use App\Http\Controllers\Auth\DevMobileLoginController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventParticipantController;
use App\Http\Controllers\FormTemplateController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LiveContentController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\QuotesController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SocialController;
use App\Http\Controllers\SpaController;
use App\Http\Controllers\SsoController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\WellnessActivityController;
use App\Http\Controllers\WellnessActivityScheduleController;
use App\Http\Controllers\WellnessActivityTypeController;
use App\Http\Controllers\WellnessBlacklistController;
use App\Http\Controllers\WellnessRegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('dbauth', [SsoController::class, 'dbauth']);
Route::get('dbauthlms', [SsoController::class, 'dbauthlms']);
Route::get('dbauthcmpr', [SsoController::class, 'dbauthcmpr']);
Route::get('dbauthexpl', [SsoController::class, 'dbauthexpl']);

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
});

// Local stand-in for the Darwinbox -> auth-service handshake that gives the
// employee SPA its JWT. Registered above the catch-all below, which would
// otherwise swallow the path and render the SPA shell instead. Both routes
// 404 without DEVELOPMENT_MODE=keydevelopment in .env.
Route::middleware('dev.mode')->group(function () {
    Route::get('dev/mobile-login', [DevMobileLoginController::class, 'create'])
        ->name('dev.mobile.login');
    Route::post('dev/mobile-login', [DevMobileLoginController::class, 'store'])
        ->name('dev.mobile.login.store');
});

// Gambar unggahan, dilayani dari storage/app/public.
//
// HARUS di atas catch-all SPA di bawahnya. Constraint catch-all itu ikut
// mencocokkan garis miring, jadi ketika route ini ditaruh sesudahnya setiap
// /images/... ditelan SpaController dan tag <img> menerima HTML shell -- yang
// tampil sebagai gambar rusak. Itu sebabnya banner aktivitas wellness kosong.
//
// Parameter-nya ikut mencocokkan garis miring supaya path bersarang hasil
// simpanan lama ("assets/images/news/news_4.jpg") juga terlayani.
Route::get('/images/{path}', ImageController::class)
    ->where('path', '.*')
    ->name('images.show');

Route::get('/{any?}', SpaController::class)->where('any', '^(?!admin).*$');

Route::prefix('admin')->group(function () {

    // Reachable without 'auth' so the language can also be changed on the
    // login screen; the middleware itself only reads a session value.
    Route::get('language/{locale}', [LanguageController::class, 'switchLanguage'])
        ->name('language.switch');

    Route::middleware('auth', 'locale', 'notification')->group(function () {

        // News
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        Route::middleware(['permission:viewmenunews'])->group(function () {
            // News
            Route::get('/news', [NewsController::class, 'index'])->name('admin.news.index');
            Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
            Route::post('/news/store', [NewsController::class, 'store'])->name('news.store');
            Route::get('/news/edit/{id}', [NewsController::class, 'edit'])->name('news.edit');
            Route::put('/news/{id}', [NewsController::class, 'update'])->name('news.update');
            Route::delete('/news/{id}/archive', [NewsController::class, 'archive'])->name('news.archive');
        });

        Route::middleware(['permission:viewmenuevent'])->group(function () {
            // Event
            Route::get('/events', [EventController::class, 'index'])->name('admin.events.index');
            Route::get('/events/create', [EventController::class, 'create'])->name('admin.events.create');
            Route::post('/events/store', [EventController::class, 'store'])->name('events.store');
            Route::delete('/events/{id}/archive', [EventController::class, 'softDelete'])->name('events.softDelete');
            Route::delete('/events/{id}/removeEvoParticipants', [EventController::class, 'removeEvoParticipants'])->name('events.removeEvoParticipants');
            Route::post('/events/{id}/close', [EventController::class, 'closeRegistration'])->name('events.close');
            Route::post('/events/{id}/toggle-status', [EventController::class, 'toggleStatus'])->name('events.toggle-status');
            Route::get('/events/{id}/edit', [EventController::class, 'edit'])->name('events.edit');
            Route::put('/events/{id}', [EventController::class, 'update'])->name('events.update');
            Route::get('/events/{encryptedId}/participants', [EventParticipantController::class, 'listParticipants'])->name('events.participants');

            Route::get('/employees/search', [EventParticipantController::class, 'search'])->name('employees.search');
            Route::post('/events/{event}/participants', [EventParticipantController::class, 'store'])->name('participants.store');

            Route::post('/participants/{id}/approve', [EventParticipantController::class, 'approve'])->name('participants.approve');
            Route::post('/participants/{id}/reject', [EventParticipantController::class, 'reject'])->name('participants.reject');
            Route::get('/ticket/qr-png/{encryptedId}', [EventController::class, 'showQRPNG'])->name('event.qrpng');
            Route::get('/participants/export/{event_id}', [EventParticipantController::class, 'export'])->name('participants.export');
            Route::post('/participants/bulk-approve', [EventParticipantController::class, 'bulkApprove'])->name('participants.bulkApprove');
        });

        Route::middleware(['permission:viewmenuevent'])->group(function () {
            Route::get('/evo', [EventController::class, 'evoIndex'])->name('admin.evo.index');
            Route::get('/evo/{id}/manage', [EventController::class, 'evoManage'])->name('admin.evo.manage');
            Route::put('/evo/{id}', [EventController::class, 'evoUpdate'])->name('evo.update');
            Route::get('/evo/export', [EventController::class, 'exportEvoParticipants'])->name('evo.export');
        });

        Route::middleware(['permission:viewmenusurvey'])->group(function () {
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
        });

        Route::middleware(['permission:viewmenusocial'])->group(function () {
            // Social
            Route::get('/social', [SocialController::class, 'index'])->name('admin.social.index');
            Route::post('/social/store', [SocialController::class, 'store'])->name('social.store');
            Route::delete('/social/{id}/delete', [SocialController::class, 'destroy'])->name('social.destroy');
            Route::put('/social/{id}', [SocialController::class, 'update'])->name('social.update');
        });

        Route::middleware(['permission:viewmenulive'])->group(function () {
            // Live
            Route::get('/live', [LiveContentController::class, 'index'])->name('live.index');
            Route::post('/live/store', [LiveContentController::class, 'store'])->name('live.store');
            Route::delete('/live/{id}', [LiveContentController::class, 'destroy'])->name('live.destroy');
        });

        Route::middleware(['permission:viewmenuquotes'])->group(function () {
            // Quotes
            Route::get('/quotes', [QuotesController::class, 'index'])->name('admin.quotes.index');
            Route::post('/quotes/store', [QuotesController::class, 'store'])->name('quotes.store');
            Route::put('/quotes/{id}', [QuotesController::class, 'update'])->name('quotes.update');
            Route::delete('/quotes/{id}/delete', [QuotesController::class, 'destroy'])->name('quotes.destroy');
        });

        Route::middleware(['permission:viewmenuform'])->group(function () {
            // Form Builder
            Route::get('/formbuilder', [FormTemplateController::class, 'index'])->name('form.index');
            Route::get('/formbuilder/create', [FormTemplateController::class, 'create'])->name('form.create');
            Route::post('/form-builder/store', [FormTemplateController::class, 'store'])->name('form-builder.store');
            Route::delete('/formbuilder/archive/{id}', [FormTemplateController::class, 'archive'])->name('formbuilder.archive');
            Route::get('/formbuilder/{id}/edit', [FormTemplateController::class, 'edit'])->name('formbuilder.edit');
            Route::put('/formbuilder/{id}', [FormTemplateController::class, 'update'])->name('formbuilder.update');
            Route::get('/forms/{id}/schema', [FormTemplateController::class, 'getSchema']);
        });

        Route::middleware(['permission:viewmenuwellness'])->group(function () {
            // Wellness -- activity types (master data)
            Route::middleware(['permission:viewmenuwellnesstype'])->group(function () {
                Route::get('/wellness/types', [WellnessActivityTypeController::class, 'index'])->name('admin.wellness.types.index');
                Route::post('/wellness/types', [WellnessActivityTypeController::class, 'store'])->name('wellness.types.store');
                Route::put('/wellness/types/{encryptedId}', [WellnessActivityTypeController::class, 'update'])->name('wellness.types.update');
                Route::delete('/wellness/types/{encryptedId}/archive', [WellnessActivityTypeController::class, 'archive'])->name('wellness.types.archive');
                Route::post('/wellness/types/{encryptedId}/restore', [WellnessActivityTypeController::class, 'restore'])->name('wellness.types.restore');

                // Attendance QR lives on the type: one printed code covers every
                // session of every activity of that type.
                Route::get('/wellness/types/{encryptedId}/qr', [WellnessActivityTypeController::class, 'qr'])->name('wellness.types.qr');
                Route::post('/wellness/types/{encryptedId}/rotate-qr', [WellnessActivityTypeController::class, 'rotateQr'])->name('wellness.types.rotateQr');
            });

            // Wellness -- activities
            Route::get('/wellness/activities', [WellnessActivityController::class, 'index'])->name('admin.wellness.activities.index');
            Route::get('/wellness/activities/create', [WellnessActivityController::class, 'create'])->name('wellness.activities.create');
            Route::post('/wellness/activities', [WellnessActivityController::class, 'store'])->name('wellness.activities.store');
            Route::get('/wellness/activities/{encryptedId}/edit', [WellnessActivityController::class, 'edit'])->name('wellness.activities.edit');
            Route::put('/wellness/activities/{encryptedId}', [WellnessActivityController::class, 'update'])->name('wellness.activities.update');
            Route::delete('/wellness/activities/{encryptedId}/archive', [WellnessActivityController::class, 'archive'])->name('wellness.activities.archive');
            Route::post('/wellness/activities/{encryptedId}/restore', [WellnessActivityController::class, 'restore'])->name('wellness.activities.restore');

            // Wellness -- schedules (sessions of one activity)
            Route::get('/wellness/activities/{encryptedId}/schedules', [WellnessActivityScheduleController::class, 'index'])->name('admin.wellness.schedules.index');
            Route::post('/wellness/activities/{encryptedId}/schedules', [WellnessActivityScheduleController::class, 'store'])->name('wellness.schedules.store');
            Route::put('/wellness/schedules/{encryptedId}', [WellnessActivityScheduleController::class, 'update'])->name('wellness.schedules.update');
            Route::delete('/wellness/schedules/{encryptedId}/archive', [WellnessActivityScheduleController::class, 'archive'])->name('wellness.schedules.archive');

            // Wellness -- participants of one schedule
            Route::get('/wellness/schedules/{encryptedId}/participants', [WellnessRegistrationController::class, 'index'])->name('admin.wellness.registrations.index');
            Route::post('/wellness/schedules/{encryptedId}/participants', [WellnessRegistrationController::class, 'store'])->name('wellness.registrations.store');
            Route::get('/wellness/schedules/{encryptedId}/export', [WellnessRegistrationController::class, 'export'])->name('wellness.registrations.export');
            Route::post('/wellness/registrations/{encryptedId}/confirm', [WellnessRegistrationController::class, 'confirm'])->name('wellness.registrations.confirm');
            Route::post('/wellness/registrations/{encryptedId}/requeue', [WellnessRegistrationController::class, 'requeue'])->name('wellness.registrations.requeue');
            Route::post('/wellness/registrations/{encryptedId}/blacklist', [WellnessRegistrationController::class, 'blacklist'])->name('wellness.registrations.blacklist');
            Route::post('/wellness/registrations/{encryptedId}/cancel', [WellnessRegistrationController::class, 'cancel'])->name('wellness.registrations.cancel');
            Route::post('/wellness/registrations/bulk-confirm', [WellnessRegistrationController::class, 'bulkConfirm'])->name('wellness.registrations.bulkConfirm');

            // Wellness -- blacklist master list
            Route::get('/wellness/blacklist', [WellnessBlacklistController::class, 'index'])->name('admin.wellness.blacklist.index');
            Route::post('/wellness/blacklist', [WellnessBlacklistController::class, 'store'])->name('wellness.blacklist.store');
            Route::put('/wellness/blacklist/{encryptedId}', [WellnessBlacklistController::class, 'update'])->name('wellness.blacklist.update');
            Route::post('/wellness/blacklist/{encryptedId}/lift', [WellnessBlacklistController::class, 'lift'])->name('wellness.blacklist.lift');
            Route::delete('/wellness/blacklist/{encryptedId}/archive', [WellnessBlacklistController::class, 'archive'])->name('wellness.blacklist.archive');
            Route::post('/wellness/blacklist/{encryptedId}/restore', [WellnessBlacklistController::class, 'restore'])->name('wellness.blacklist.restore');
            Route::get('/wellness/employees/search', [WellnessRegistrationController::class, 'searchEmployees'])->name('wellness.employees.search');
        });

        Route::middleware(['permission:viewroleem'])->group(function () {
            // Roles
            Route::get('/roles', [RoleController::class, 'index'])->name('roles');
            Route::post('/roles/submit', [RoleController::class, 'store'])->name('roles.store');
            Route::post('/roles/update', [RoleController::class, 'update'])->name('roles.update');
            Route::delete('/roles/delete/{id}', [RoleController::class, 'destroy'])->name('roles.delete');
            Route::get('/roles/assign', [RoleController::class, 'assign'])->name('roles.assign');
            Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::get('/roles/manage', [RoleController::class, 'manage'])->name('roles.manage');
            Route::get('/roles/get-assignment', [RoleController::class, 'getAssignment'])->name('getAssignment');
            Route::get('/roles/get-permission', [RoleController::class, 'getPermission'])->name('getPermission');
            Route::post('/assign-user', [RoleController::class, 'assignUser'])->name('assign.user');
        });

        Route::get('{first}/{second}', [HomeController::class, 'secondLevel'])->name('second');
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    });

    // Login stays outside the 'auth' group: the auth middleware redirects
    // unauthenticated visitors here, so this route must be reachable by them.
    // Without DEVELOPMENT_MODE=keydevelopment in .env it only bounces the
    // browser to Darwinbox as before; with it, a local login form is rendered
    // so the admin pages can be reached without the SSO handshake.
    Route::get('login', [DevLoginController::class, 'create'])
        ->middleware('locale')
        ->name('login');
    Route::post('login', [DevLoginController::class, 'store'])
        ->middleware('dev.mode')
        ->name('dev.login.store');

    Route::fallback(AdminFallbackController::class);

    require __DIR__.'/auth.php';
});
