<?php

use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\MobileDriverController;
use App\Http\Controllers\DriverAvailabilityController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/login', [MobileAuthController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware(['auth:sanctum', 'active'])->group(function (): void {
        Route::get('/me', [MobileAuthController::class, 'me'])->middleware('abilities:profile:read');
        Route::delete('/auth/logout', [MobileAuthController::class, 'logout']);
        Route::get('/driver/home', [MobileDriverController::class, 'home'])->middleware(['role:conductor', 'abilities:driver:trips']);
        Route::post('/driver/trips/{tripId}/accept', [MobileDriverController::class, 'accept'])->whereNumber('tripId')->middleware(['role:conductor', 'abilities:driver:trips']);
        Route::post('/driver/trips/{tripId}/reject', [MobileDriverController::class, 'reject'])->whereNumber('tripId')->middleware(['role:conductor', 'abilities:driver:trips']);
        Route::post('/driver/trips/{tripId}/start', [MobileDriverController::class, 'start'])->whereNumber('tripId')->middleware(['role:conductor', 'abilities:driver:trips']);
        Route::post('/driver/trips/{tripId}/complete', [MobileDriverController::class, 'complete'])->whereNumber('tripId')->middleware(['role:conductor', 'abilities:driver:trips']);
        Route::post('/driver/availability', [DriverAvailabilityController::class, 'update'])
            ->middleware(['role:conductor', 'abilities:driver:location', 'throttle:10,1']);
        Route::post('/driver/location', [DriverAvailabilityController::class, 'location'])
            ->middleware(['role:conductor', 'abilities:driver:location', 'throttle:30,1']);
    });
});