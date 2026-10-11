<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\DispatchService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    app(DispatchService::class)->expireAndRedispatch();
    app(DispatchService::class)->dispatchWaiting();
})->name('dispatch-trips')->everyMinute()->withoutOverlapping();

Schedule::command('ops:check --json')->everyFiveMinutes()->withoutOverlapping()->appendOutputTo(storage_path('logs/operations.log'));
Schedule::call(function(){ App\Models\LivenessChallenge::where('expires_at','<',now()->subDay())->delete(); App\Models\TripShareToken::where('expires_at','<',now()->subDays(7))->delete(); })->daily()->name('purge-expired-security-artifacts')->withoutOverlapping();

Schedule::call(fn() => app(App\Services\TripSafetyMonitor::class)->inspectStaleTrips())->name('safety-monitor')->everyMinute()->withoutOverlapping();
