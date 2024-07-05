<?php

use App\Http\Controllers\Admin\ForecastController;
use App\Http\Controllers\Admin\LogController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('/forecasts', [ForecastController::class, 'index'])->name('admin.forecasts');
    Route::get('/logs', [LogController::class, 'showLaravelLog'])->name('admin.forecasts');
});
