<?php

use Illuminate\Support\Facades\Route;
use Kolgaev\Tube\Http\Controllers\TubeController;

Route::group(['prefix' => "tube/{process}"], function () {

    Route::any('webhook', [TubeController::class, 'webhook'])
        ->name('kolgaev.tube.webhook');

    Route::post('download', [TubeController::class, 'download'])
        ->name('kolgaev.tube.download');

    Route::get('file/{file}', [TubeController::class, 'file'])
        ->name('kolgaev.tube.download.file');

    Route::delete('delete', [TubeController::class, 'delete'])
        ->name('kolgaev.tube.file.delete');
});
