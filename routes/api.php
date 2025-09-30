<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\LiveContentController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\QuotesController;
use App\Http\Controllers\Api\SocialController;
use App\Http\Controllers\Api\SurveyVoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;


Route::get('auth-service', [AuthController::class, 'login']);

Route::middleware('auth:api')->get('/verify', function (Request $request) {
    return response()->json($request->user());
});


Route::middleware('auth.token')->group(function () {
    Route::get('/profile', [EmployeeController::class, 'profile']);
    Route::get('/events', [EventController::class, 'getEvents']);
    Route::get('/my-event', [EventController::class, 'myEvents']);
    Route::get('/get-evo', [EventController::class, 'getEvo']);
    Route::get('/events/{id}', [EventController::class, 'getEventDetails']);
    Route::post('/event-confirmation', [EventController::class, 'eventConfirmation']);
    Route::post('/event-attendance', [EventController::class, 'eventAttendance']);
    Route::get('/event-form/{id}', [EventController::class, 'getEventForm']);
    Route::get('/events/check-registration/{id}', [EventController::class, 'checkRegistration']);
    Route::post('/event-registration', [EventController::class, 'store']);
    Route::get('/survey-vote', [SurveyVoteController::class, 'getSurveyVotes']);
    Route::get('/survey-vote/{id}', [SurveyVoteController::class, 'getSurveyVotesDetails']);
    Route::get('/survey-form/{id}', [SurveyVoteController::class, 'getSurveyForm']);
    Route::post('/survey', [SurveyVoteController::class, 'store']);
    Route::get('/voting-result/{id}', [SurveyVoteController::class, 'getVotingResult']);
    Route::get('/news', [NewsController::class, 'getNews']);
    Route::get('/news/{id}', [NewsController::class, 'getNewsDetails']);
    Route::post('/news/{id}/view', [NewsController::class, 'recordView']);
    Route::post('/news/{id}/like', [NewsController::class, 'like']);
    Route::delete('/news/{id}/like', [NewsController::class, 'unlike']);
    Route::get('/social', [SocialController::class, 'index']);
    Route::get('/quotes', [QuotesController::class, 'getQuotes']);
    Route::get('/live-content', [LiveContentController::class, 'getLiveContent']);
});