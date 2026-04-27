<?php
declare(strict_types=1);

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfileExportController;
use App\Http\Controllers\ProfileSearchController;


Route::middleware(['throttle:api-user'])->group(function () {

    Route::middleware(['auth:sanctum', 'active'])->group(function () {

        Route::get('/profiles/search', ProfileSearchController::class)
            ->name('profiles.search');

        Route::get('/profiles/export', ProfileExportController::class)
            ->name('profiles.export');

        Route::resource('profiles', ProfileController::class)
            ->except(['create', 'edit']);


    });
});
