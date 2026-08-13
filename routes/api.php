<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::namespace('Api')->name('api.')->group(function (): void {
    Route::resource('exercises', 'ExerciseController')->only(['show']);
    Route::resource('exercises.check', 'Exercise\CheckController')->only(['store'])->middleware('throttle:10,1');
    Route::resource('exercises.solutions', 'Exercise\SolutionController')->only(['store']);
});
