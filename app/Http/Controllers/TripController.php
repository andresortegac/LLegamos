<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Trip;
use App\Models\User;
use App\Services\RoutePricingService;
use App\Services\MonetizationService;
use App\Services\FraudDetectionService;
use App\Services\DispatchService;
use App\Services\RealtimePublisher;
use App\Models\TripOffer;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class TripController extends Controller
{
    public function __construct(private readonly RoutePricingService $pricing, private readonly MonetizationService $monetization, private readonly FraudDetectionService $fraud, private readonly DispatchService $dispatch, private readonly RealtimePublisher $realtime)
    {
    }
    // Mostrar formulario para solicitar viaje (Pasajero)
    public function createRequest()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        if ($user['role'] !== 'pasajero') {
            return redirect()->route('dashboard');
        }

        return view('trip.request-ride', ['user' => $user]);
    }

    // Guardar solicitud de viaje (Pasajero)
    public function storeRequest(Request $request)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        if ($user['role'] !== 'pasajero') {
            return redirect()->route('dashboard');
        }

        $departmentsData = $this->getDepartments();
        $departments = collect($departmentsData)
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        if (empty($departments)) {
            return back()
                ->withInput()
                ->withErrors(['department' => 'No se pudo cargar el catalogo de departamentos. Intenta nuevamente.']);
        }

        $selectedDepartment = $request->input('department');

        $selectedDepartmentData = collect($departmentsData)
            ->first(fn ($department) => ($department['name'] ?? null) === $selectedDepartment);

        $municipalitiesData = !empty($selectedDepartmentData['id'])
            ? $this->getMunicipalitiesByDepartmentId((int) $selectedDepartmentData['id'])
            : [];

        $municipalities = collect($municipalitiesData)
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        $validated = $request->validate([
            'vehicle_type' => ['required', Rule::in(['carro', 'moto'])],
            'department' => ['required', Rule::in($departments)],
            'municipality' => ['required', Rule::in($municipalities)],
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'origin_lat' => 'required|numeric|between:-90,90',
            'origin_lng' => 'required|numeric|between:-180,180',
            'destination_lat' => 'required|numeric|between:-90,90',
            'destination_lng' => 'required|numeric|between:-180,180',
            'notes' => 'nullable|string|max:500',
        ], [
            'vehicle_type.required' => 'Debes seleccionar si deseas carro o moto.',
            'vehicle_type.in' => 'El tipo de vehiculo seleccionado no es valido.',
            'department.required' => 'El departamento es obligatorio.',
            'department.in' => 'Selecciona un departamento valido.',
            'municipality.required' => 'El municipio es obligatorio.',
            'municipality.in' => 'Selecciona un municipio valido para el departamento.',
            'origin.required' => 'El punto de recogida es obligatorio.',
            'destination.required' => 'El destino es obligatorio.',
            'notes.max' => 'Las notas no pueden exceder 500 caracteres.',
        ]);

        $validated['origin'] = $this->normalizeAddress(
            $validated['origin'],
            $validated['municipality'],
            $validated['department']
        );

        $validated['destination'] = $this->normalizeAddress(
            $validated['destination'],
            $validated['municipality'],
            $validated['department']
        );

        $validated['passenger_id'] = $user['id'];
        $validated['status'] = 'pending';
        $validated['pickup_pin'] = (string) random_int(1000, 9999);
        
        $validated = array_merge($validated, $this->pricing->quote(
            $validated['vehicle_type'],
            (float) $validated['origin_lat'],
            (float) $validated['origin_lng'],
            (float) $validated['destination_lat'],
            (float) $validated['destination_lng']
        ));

        $trip = Trip::create($validated);
        $this->dispatch->dispatch($trip);

        return redirect()->route('trip.show', $trip->id)->with('success', 'Viaje solicitado correctamente.');
    }

    private function normalizeAddress(string $address, string $municipality, string $department): string
    {
        $normalized = trim(preg_replace('/\s+/', ' ', $address));

        $suffix = "{$municipality}, {$department}";
        if (stripos($normalized, $municipality) !== false || stripos($normalized, $department) !== false) {
            return $normalized;
        }

        return "{$normalized}, {$suffix}";
    }

    public function departments()
    {
        if (!Session::has('user')) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        return response()->json($this->getDepartments());
    }

    public function municipalities(int $departmentId)
    {
        if (!Session::has('user')) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        return response()->json($this->getMunicipalitiesByDepartmentId($departmentId));
    }

    private function getDepartments(): array
    {
        return Cache::remember('colombia_departments_catalog', now()->addHours(12), function () {
            try {
                $response = Http::timeout(15)->get('https://api-colombia.com/api/v1/Department');
            } catch (\Throwable $e) {
                return [];
            }

            if (!$response->successful()) {
                return [];
            }

            return collect($response->json())
                ->map(function ($department) {
                    return [
                        'id' => $department['id'] ?? null,
                        'name' => $department['name'] ?? null,
                    ];
                })
                ->filter(fn ($department) => !empty($department['id']) && !empty($department['name']))
                ->sortBy('name')
                ->values()
                ->all();
        });
    }

    private function getMunicipalitiesByDepartmentId(int $departmentId): array
    {
        return Cache::remember("colombia_municipalities_catalog_{$departmentId}", now()->addHours(12), function () use ($departmentId) {
            try {
                $response = Http::timeout(20)->get("https://api-colombia.com/api/v1/Department/{$departmentId}/cities");
            } catch (\Throwable $e) {
                return [];
            }

            if (!$response->successful()) {
                return [];
            }

            return collect($response->json())
                ->map(function ($city) {
                    return [
                        'name' => $city['name'] ?? null,
                    ];
                })
                ->filter(fn ($city) => !empty($city['name']))
                ->sortBy('name')
                ->values()
                ->all();
        });
    }

    // Calcular distancia entre dos puntos (Fórmula de Haversine)
    private function calculateDistance($lat1, $lng1, $lat2, $lng2)
    {
        $earth_radius = 6371; // km

        $lat1_rad = deg2rad($lat1);
        $lat2_rad = deg2rad($lat2);
        $delta_lat = deg2rad($lat2 - $lat1);
        $delta_lng = deg2rad($lng2 - $lng1);

        $a = sin($delta_lat / 2) * sin($delta_lat / 2) +
             cos($lat1_rad) * cos($lat2_rad) *
             sin($delta_lng / 2) * sin($delta_lng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earth_radius * $c;
    }

    // Ver viaje activo (Pasajero y Conductor)
    public function show($id)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');
        $trip = Trip::find($id);

        if (!$trip) {
            return redirect()->route('dashboard')->with('error', 'Viaje no encontrado.');
        }

        // Validar que solo el pasajero o conductor pueden ver
        if ($trip->passenger_id !== $user['id'] && $trip->driver_id !== $user['id']) {
            return redirect()->route('dashboard')->with('error', 'No tienes permiso para ver este viaje.');
        }

        return view('trip.show', ['user' => $user, 'trip' => $trip]);
    }

    public function liveStatus(Request $request, int $id)
    {
        $trip = Trip::with('driver.driverProfile')->findOrFail($id);
        $user = $request->user();

        if ($trip->passenger_id !== $user->id && $trip->driver_id !== $user->id) {
            abort(403);
        }

        $profile = $trip->driver?->driverProfile;
        $locationAge = $profile?->location_updated_at
            ? max(0, now()->diffInSeconds($profile->location_updated_at))
            : null;
        $locationIsFresh = $locationAge !== null && $locationAge <= 45 && (bool) $profile->is_online;
        $targetIsDestination = $trip->status === 'in_progress';
        $targetLat = $targetIsDestination ? $trip->destination_lat : $trip->origin_lat;
        $targetLng = $targetIsDestination ? $trip->destination_lng : $trip->origin_lng;
        $distanceToTarget = $locationIsFresh && $targetLat && $targetLng
            ? round($this->calculateDistance((float) $profile->current_lat, (float) $profile->current_lng, (float) $targetLat, (float) $targetLng), 2)
            : null;
        $etaToTarget = $distanceToTarget !== null ? max(1, (int) ceil(($distanceToTarget / 25) * 60)) : null;

        return response()->json([
            'status' => $trip->status,
            'driver' => $trip->driver ? [
                'name' => $trip->driver->name,
                'latitude' => $locationIsFresh ? (float) $profile->current_lat : null,
                'longitude' => $locationIsFresh ? (float) $profile->current_lng : null,
                'location_updated_at' => $locationIsFresh ? $profile->location_updated_at->toIso8601String() : null,
                'location_age_seconds' => $locationAge,
                'location_fresh' => $locationIsFresh,
                'is_online' => (bool) $profile->is_online,
                'accuracy' => $profile->location_accuracy_m !== null ? (float) $profile->location_accuracy_m : null,
                'heading' => $profile->location_heading !== null ? (float) $profile->location_heading : null,
                'speed' => $profile->location_speed_mps !== null ? (float) $profile->location_speed_mps : null,
                'trip_stage' => $targetIsDestination ? 'to_destination' : 'to_pickup',
                'target_label' => $targetIsDestination ? 'Destino' : 'Punto de recogida',
                'distance_to_target_km' => $distanceToTarget,
                'eta_to_target_minutes' => $etaToTarget,
                'distance_to_pickup_km' => $targetIsDestination ? null : $distanceToTarget,
                'eta_to_pickup_minutes' => $targetIsDestination ? null : $etaToTarget,
                'vehicle' => [
                    'type' => $trip->vehicle_type,
                    'make' => $profile->vehicle_make,
                    'model' => $profile->vehicle_model,
                    'color' => $profile->vehicle_color,
                    'plate' => $profile->vehicle_plate,
                ],
            ] : null,
            'safety_alerts' => $trip->incidents()
                ->where('status', 'open')
                ->where('description', 'like', '[AUTOMATICA]%')
                ->latest()
                ->limit(3)
                ->get(['id', 'severity', 'description', 'created_at']),
            'updated_at' => $trip->updated_at->toIso8601String(),
        ]);
    }
    // Listar viajes pendientes (Conductor)
    public function listPending()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        if ($user['role'] !== 'conductor') {
            return redirect()->route('dashboard');
        }

        if ($redirect = $this->ensureApprovedDriver($user)) {
            return $redirect;
        }

        $profile = User::find($user['id'])?->driverProfile;
        if (!$profile?->is_online || !$profile->current_lat || !$profile->current_lng) {
            return redirect()->route('dashboard.conductor')
                ->with('error', 'Debes conectarte y compartir tu ubicacion para ver viajes cercanos.');
        }

        $this->dispatch->expireAndRedispatch();
        $offers = TripOffer::with('trip')
            ->where('driver_id', $user['id'])
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->orderBy('expires_at')
            ->get();
        $trips = $offers->map(function (TripOffer $offer) {
            $trip = $offer->trip;
            $trip->driver_distance_km = $offer->distance_km;
            $trip->dispatch_offer_id = $offer->id;
            $trip->dispatch_expires_at = $offer->expires_at;
            return $trip;
        });

        return view('trip.list-pending', ['user' => $user, 'trips' => $trips]);
    }

    // Aceptar viaje (Conductor)
    public function accept($id)
    {
        $user = Session::get('user');
        abort_unless($user && $user['role'] === 'conductor', 403);
        if ($redirect = $this->ensureApprovedDriver($user)) return $redirect;
        $profile = User::find($user['id'])?->driverProfile;
        abort_unless($profile?->is_online && $profile->location_updated_at?->greaterThan(now()->subSeconds(config('dispatch.location_fresh_seconds'))), 422, 'Debes estar conectado y compartir una ubicacion reciente.');
        abort_if(Trip::where('driver_id',$user['id'])->whereIn('status',['accepted','in_progress'])->exists(),409,'Ya tienes un viaje activo.');
        $offer = TripOffer::where('trip_id',$id)->where('driver_id',$user['id'])->where('status','pending')->latest()->firstOrFail();
        $trip = $this->dispatch->accept($offer,(int)$user['id']);
        return redirect()->route('trip.show',$trip->id)->with('success','Viaje aceptado.');
    }

    public function rejectOffer(int $id)
    {
        $user=Session::get('user');
        abort_unless($user && $user['role']==='conductor',403);
        $offer=TripOffer::where('trip_id',$id)->where('driver_id',$user['id'])->where('status','pending')->latest()->firstOrFail();
        $this->dispatch->reject($offer,(int)$user['id']);
        return redirect()->route('trip.list-pending')->with('success','Oferta rechazada. Buscaremos otro conductor.');
    }

    // Iniciar viaje (Conductor)
    public function start($id)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        if ($user['role'] === 'conductor' && ($redirect = $this->ensureApprovedDriver($user))) {
            return $redirect;
        }

        $validated = request()->validate([
            'pickup_pin' => 'required|digits:4',
        ]);

        $trip = Trip::whereKey($id)
            ->where('driver_id', $user['id'])
            ->where('status', 'accepted')
            ->first();

        if (!$trip || !hash_equals((string) $trip->pickup_pin, (string) $validated['pickup_pin'])) {
            return back()->withErrors(['pickup_pin' => 'El PIN de inicio no es correcto.']);
        }

        $started = Trip::whereKey($id)
            ->where('driver_id', $user['id'])
            ->where('status', 'accepted')
            ->update([
                'status' => 'in_progress',
                'start_time' => now(),
            ]);

        if ($started !== 1) {
            return back()->with('error', 'No puedes iniciar este viaje.');
        }

        $this->realtime->trip(Trip::findOrFail($id), 'trip.updated', ['status'=>'in_progress']);
        return redirect()->route('trip.show', $id)->with('success', 'Viaje iniciado.');
    }

    // Completar viaje (Conductor)
    public function complete($id)
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        if ($user['role'] === 'conductor' && ($redirect = $this->ensureApprovedDriver($user))) {
            return $redirect;
        }

        $completed = Trip::whereKey($id)
            ->where('driver_id', $user['id'])
            ->where('status', 'in_progress')
            ->update([
                'status' => 'completed',
                'end_time' => now(),
                'final_cost' => \Illuminate\Support\Facades\DB::raw('estimated_cost'),
            ]);

        if ($completed !== 1) {
            return back()->with('error', 'No puedes completar este viaje.');
        }

        $trip = Trip::findOrFail($id);
        $this->monetization->recordCompletedTrip($trip);
        UserNotification::create(['user_id'=>$trip->passenger_id,'type'=>'trip_completed','title'=>'Viaje completado','message'=>'Tu viaje #'.$id.' finalizo. Puedes calificar y registrar el pago.','action_url'=>route('trip.show',$id)]);
        $this->realtime->trip($trip, 'trip.updated', ['status'=>'completed']);
        return redirect()->route('trip.show', $id)->with('success', 'Viaje completado.');
    }

    public function cancel(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:500',
        ]);

        $user = Session::get('user');
        $trip = Trip::findOrFail($id);
        $isPassenger = $user['role'] === 'pasajero' && $trip->passenger_id === $user['id'];
        $isDriver = $user['role'] === 'conductor' && $trip->driver_id === $user['id'];

        if ((!$isPassenger && !$isDriver) || !in_array($trip->status, ['pending', 'accepted'], true)) {
            abort(403, 'Este viaje no puede ser cancelado por tu usuario.');
        }

        $cancelled = Trip::whereKey($trip->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->update([
                'status' => 'cancelled',
                'cancelled_by' => $user['role'],
                'cancellation_reason' => trim($validated['reason']),
                'cancelled_at' => now(),
            ]);

        if ($cancelled !== 1) {
            return back()->with('error', 'El estado del viaje cambio antes de cancelarlo.');
        }

        $recipientId = $isPassenger ? $trip->driver_id : $trip->passenger_id;
        if ($recipientId) {
            UserNotification::create([
                'user_id' => $recipientId,
                'type' => 'trip_cancelled',
                'title' => 'Viaje cancelado',
                'message' => 'El viaje #' . $trip->id . ' fue cancelado.',
                'action_url' => route('trip.show', $trip->id),
            ]);
        }

        $trip->refresh();
        $this->realtime->trip($trip, 'trip.updated', ['status'=>'cancelled']);
        return redirect()->route('dashboard')->with('success', 'Viaje cancelado correctamente.');
    }
    // Historial de viajes del usuario
    public function history()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $user = Session::get('user');

        if ($user['role'] === 'pasajero') {
            $trips = Trip::where('passenger_id', $user['id'])
                ->where('status', 'completed')
                ->orderBy('end_time', 'desc')
                ->get();
        } else {
            if ($redirect = $this->ensureApprovedDriver($user)) {
                return $redirect;
            }

            $trips = Trip::where('driver_id', $user['id'])
                ->where('status', 'completed')
                ->orderBy('end_time', 'desc')
                ->get();
        }

        return view('trip.history', ['user' => $user, 'trips' => $trips]);
    }

    private function ensureApprovedDriver(array $user)
    {
        $driver = User::with('driverProfile')->find($user['id']);
        $profile = $driver?->driverProfile;

        if (!$profile || $profile->verification_status !== 'approved') {
            return redirect()
                ->route('driver-profile.show')
                ->with('error', 'Debes completar el registro y esperar aprobacion del administrador para operar como conductor.');
        }

        return null;
    }
}
