<?php

use App\Http\Controllers\Admin\ForecastController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::get('/forecasts', [ForecastController::class, 'index'])->name('admin.forecasts');
});
