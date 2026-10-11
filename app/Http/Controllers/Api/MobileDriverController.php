<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripOffer;
use App\Models\UserNotification;
use App\Services\DispatchService;
use App\Services\MonetizationService;
use App\Services\RealtimePublisher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileDriverController extends Controller
{
    public function __construct(
        private readonly DispatchService $dispatch,
        private readonly MonetizationService $monetization,
        private readonly RealtimePublisher $realtime,
    ) {}

    public function home(Request $request): JsonResponse
    {
        $profile = $this->profile($request);
        $this->dispatch->expireAndRedispatch();
        if ($profile->is_online) $this->dispatch->dispatchWaiting(5);

        $active = Trip::with('passenger:id,name')->where('driver_id', $request->user()->id)
            ->whereIn('status', ['accepted', 'in_progress'])->latest()->first();
        $offers = $active ? collect() : TripOffer::with('trip.passenger:id,name')
            ->where('driver_id', $request->user()->id)->where('status', 'pending')
            ->where('expires_at', '>', now())->orderBy('expires_at')->get();

        return response()->json([
            'driver' => ['is_online' => (bool) $profile->is_online, 'verification_status' => $profile->verification_status],
            'active_trip' => $active ? $this->tripPayload($active) : null,
            'offers' => $offers->map(fn (TripOffer $offer) => [
                'id' => $offer->id,
                'expires_at' => $offer->expires_at->toIso8601String(),
                'distance_to_pickup_km' => (float) $offer->distance_km,
                'trip' => $this->tripPayload($offer->trip),
            ])->values(),
        ]);
    }

    public function accept(Request $request, int $tripId): JsonResponse
    {
        $profile = $this->profile($request);
        abort_unless($profile->is_online && $profile->location_updated_at?->greaterThan(now()->subSeconds(config('dispatch.location_fresh_seconds'))), 422, 'Debes estar conectado y compartir una ubicación reciente.');
        abort_if(Trip::where('driver_id', $request->user()->id)->whereIn('status', ['accepted', 'in_progress'])->exists(), 409, 'Ya tienes un viaje activo.');
        $offer = TripOffer::where('trip_id', $tripId)->where('driver_id', $request->user()->id)->where('status', 'pending')->latest()->firstOrFail();
        $trip = $this->dispatch->accept($offer, (int) $request->user()->id)->load('passenger:id,name');
        return response()->json(['message' => 'Viaje aceptado.', 'trip' => $this->tripPayload($trip)]);
    }

    public function reject(Request $request, int $tripId): JsonResponse
    {
        $this->profile($request);
        $offer = TripOffer::where('trip_id', $tripId)->where('driver_id', $request->user()->id)->where('status', 'pending')->latest()->firstOrFail();
        $this->dispatch->reject($offer, (int) $request->user()->id);
        return response()->json(['message' => 'Oferta rechazada.']);
    }

    public function start(Request $request, int $tripId): JsonResponse
    {
        $this->profile($request);
        $validated = $request->validate(['pickup_pin' => ['required', 'digits:4']]);
        $trip = Trip::whereKey($tripId)->where('driver_id', $request->user()->id)->where('status', 'accepted')->first();
        abort_unless($trip && hash_equals((string) $trip->pickup_pin, (string) $validated['pickup_pin']), 422, 'El PIN de inicio no es correcto.');
        $changed = Trip::whereKey($tripId)->where('driver_id', $request->user()->id)->where('status', 'accepted')->update(['status' => 'in_progress', 'start_time' => now()]);
        abort_unless($changed === 1, 409, 'El estado del viaje cambió.');
        $trip->refresh()->load('passenger:id,name');
        $this->realtime->trip($trip, 'trip.updated', ['status' => 'in_progress']);
        return response()->json(['message' => 'Viaje iniciado.', 'trip' => $this->tripPayload($trip)]);
    }

    public function complete(Request $request, int $tripId): JsonResponse
    {
        $this->profile($request);
        $changed = Trip::whereKey($tripId)->where('driver_id', $request->user()->id)->where('status', 'in_progress')->update(['status' => 'completed', 'end_time' => now(), 'final_cost' => DB::raw('estimated_cost')]);
        abort_unless($changed === 1, 409, 'No puedes completar este viaje.');
        $trip = Trip::with('passenger:id,name')->findOrFail($tripId);
        $this->monetization->recordCompletedTrip($trip);
        UserNotification::create(['user_id' => $trip->passenger_id, 'type' => 'trip_completed', 'title' => 'Viaje completado', 'message' => 'Tu viaje #'.$tripId.' finalizó. Puedes calificar y registrar el pago.', 'action_url' => route('trip.show', $tripId)]);
        $this->realtime->trip($trip, 'trip.updated', ['status' => 'completed']);
        return response()->json(['message' => 'Viaje completado.', 'trip' => $this->tripPayload($trip)]);
    }

    private function profile(Request $request)
    {
        $profile = $request->user()->driverProfile;
        abort_unless($profile && $profile->verification_status === 'approved' && $profile->status === 'active', 403, 'El conductor no está habilitado para operar.');
        return $profile;
    }

    private function tripPayload(Trip $trip): array
    {
        return [
            'id' => $trip->id, 'status' => $trip->status, 'vehicle_type' => $trip->vehicle_type,
            'passenger_name' => $trip->passenger?->name, 'origin' => $trip->origin, 'destination' => $trip->destination,
            'origin_lat' => $trip->origin_lat !== null ? (float) $trip->origin_lat : null,
            'origin_lng' => $trip->origin_lng !== null ? (float) $trip->origin_lng : null,
            'destination_lat' => $trip->destination_lat !== null ? (float) $trip->destination_lat : null,
            'destination_lng' => $trip->destination_lng !== null ? (float) $trip->destination_lng : null,
            'estimated_cost' => (float) $trip->estimated_cost, 'currency' => $trip->currency ?: 'COP',
            'notes' => $trip->notes,
        ];
    }
}