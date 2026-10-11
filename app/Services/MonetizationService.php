<?php
namespace App\Services;
use App\Models\DriverEarning;
use App\Models\Trip;
class MonetizationService
{
    public function recordCompletedTrip(Trip $trip): DriverEarning
    {
        if (!$trip->driver_id || $trip->status !== 'completed') {
            throw new \LogicException('Solo un viaje completado puede generar una ganancia.');
        }
        $gross = (float) ($trip->final_cost ?? $trip->estimated_cost);
        $rate = (float) config('monetization.commission_rate', 12);
        $fee = round($gross * ($rate / 100), 2);
        return DriverEarning::firstOrCreate(['trip_id' => $trip->id], [
            'driver_id' => $trip->driver_id,
            'gross_amount' => $gross,
            'commission_rate' => $rate,
            'platform_fee' => $fee,
            'net_amount' => round($gross - $fee, 2),
            'status' => 'pending',
        ]);
    }
    public function makeAvailable(int $tripId): void
    {
        DriverEarning::where('trip_id', $tripId)->where('status', 'pending')->update(['status'=>'available','available_at'=>now()]);
    }
}