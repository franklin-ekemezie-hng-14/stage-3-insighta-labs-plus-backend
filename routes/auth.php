<?php
declare(strict_types=1);

use App\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\GitHubAuthController;

Route::middleware(['throttle:auth'])->prefix('auth')->name('auth.')->group(function () {

    /*
     * --------------------------------
     * Guest users only routes
     * ----------------------------------
     */

    Route::middleware('guest')->group(function () {

        Route::get('/github', [GithubAuthController::class, 'redirect'])
            ->name('github');
        Route::get('/github/callback', [GithubAuthController::class, 'callback'])
            ->name('github.callback');

        Route::post('/refresh', [AuthenticatedSessionController::class, 'refresh'])
            ->name('refresh');

    });


    /*
     * --------------------------------------
     * Auth users only routes
     * --------------------------------------
     */

    Route::middleware('auth:sanctum')->group(function () {


        Route::post('/logout', [AuthenticatedSessionController::class, 'logout'])
            ->name('logout');

    });

});
