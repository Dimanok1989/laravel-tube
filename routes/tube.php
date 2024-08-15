<?php

use Illuminate\Support\Facades\Route;
use Kolgaev\Tube\Http\Controllers\TubeController;

Route::group(['prefix' => "tube/{process}"], function () {

    Route::any("webhook", [TubeController::class, 'webhook']);

    Route::any("download", [TubeController::class, 'download']);
});
