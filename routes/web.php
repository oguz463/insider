<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

Route::prefix('api')->group(function () {
    Route::get('/teams', [TeamController::class, 'index']);
    Route::get('/games', [GameController::class, 'index']);
    Route::post('/generate-fixtures', [GameController::class, 'createFixtures']);
    Route::post('/play-next-week', [GameController::class, 'playNextWeek']);
    Route::post('/play-all-weeks', [GameController::class, 'playAllWeeks']);
    Route::get('/simulation-data', [GameController::class, 'getSimulationData']);
    Route::post('/reset-data', [GameController::class, 'resetData']);

});
