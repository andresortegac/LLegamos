<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\DispatchService;
use App\Services\RealtimePublisher;
use App\Services\TripSafetyMonitor;
use Illuminate\Http\Request;

class DriverAvailabilityController extends Controller
{
    public function __construct(
        private readonly DispatchService $dispatch,
        private readonly RealtimePublisher $realtime,
        private readonly TripSafetyMonitor $safety,
    ) {}

    public function update(Request $request)
    {
        abort_unless($request->user()?->role === 'conductor', 403);

        $validated = $request->validate([
            'is_online' => ['required', 'boolean'],
            'latitude' => ['nullable', 'required_if:is_online,1', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_if:is_online,1', 'numeric', 'between:-180,180'],
        ]);

        $profile = $request->user()->driverProfile;
        abort_unless($profile && $profile->verification_status === 'approved' && $profile->status === 'active', 403);

        $online = (bool) $validated['is_online'];
        $changes = ['is_online' => $online];
        if ($online) {
            $changes += [
                'current_lat' => $validated['latitude'],
                'current_lng' => $validated['longitude'],
                'location_updated_at' => now(),
            ];
        }
        $profile->update($changes);

        if ($online) {
            $this->dispatch->dispatchWaiting(5);
        }

        $message = $online
            ? 'Ahora estas en linea y puedes recibir viajes.'
            : 'Ahora estas fuera de linea.';

        if ($request->expectsJson()) {
            return response()->json([
                'is_online' => $online,
                'message' => $message,
                'updated_at' => $profile->fresh()->location_updated_at?->toIso8601String(),
            ]);
        }

        return back()->with('success', $message);
    }

    public function location(Request $request)
    {
        abort_unless($request->user()?->role === 'conductor', 403);

        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0', 'max:10000'],
            'heading' => ['nullable', 'numeric', 'between:0,360'],
            'speed' => ['nullable', 'numeric', 'min:0', 'max:150'],
        ]);

        $profile = $request->user()->driverProfile;
        abort_unless($profile && $profile->verification_status === 'approved' && $profile->status === 'active' && $profile->is_online, 403);

        $profile->update([
            'current_lat' => $validated['latitude'],
            'current_lng' => $validated['longitude'],
            'location_updated_at' => now(),
            'location_accuracy_m' => $validated['accuracy'] ?? null,
            'location_heading' => $validated['heading'] ?? null,
            'location_speed_mps' => $validated['speed'] ?? null,
        ]);

        $activeTrip = Trip::where('driver_id', $request->user()->id)
            ->whereIn('status', ['accepted', 'in_progress'])
            ->first();

        if ($activeTrip) {
            $this->safety->inspect(
                $activeTrip,
                (float) $profile->current_lat,
                (float) $profile->current_lng,
                $profile->location_speed_mps !== null ? (float) $profile->location_speed_mps : null,
            );

            $this->realtime->trip($activeTrip, 'driver.location', [
                'name' => (string) $request->user()->name,
                'latitude' => (float) $profile->current_lat,
                'longitude' => (float) $profile->current_lng,
                'updated_at' => $profile->location_updated_at->toIso8601String(),
                'location_updated_at' => $profile->location_updated_at->toIso8601String(),
                'location_fresh' => true,
                'accuracy' => $profile->location_accuracy_m !== null ? (float) $profile->location_accuracy_m : null,
                'heading' => $profile->location_heading !== null ? (float) $profile->location_heading : null,
                'speed' => $profile->location_speed_mps !== null ? (float) $profile->location_speed_mps : null,
                'trip_stage' => $activeTrip->status === 'in_progress' ? 'to_destination' : 'to_pickup',
                'target_label' => $activeTrip->status === 'in_progress' ? 'Destino' : 'Punto de recogida',
                'vehicle' => [
                    'type' => $activeTrip->vehicle_type,
                    'make' => $profile->vehicle_make,
                    'model' => $profile->vehicle_model,
                    'color' => $profile->vehicle_color,
                    'plate' => $profile->vehicle_plate,
                ],
            ]);
        }

        $this->dispatch->dispatchWaiting(5);

        return response()->json(['updated_at' => $profile->location_updated_at->toIso8601String()]);
    }
}