<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class TripController extends Controller
{
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
            'origin_lat' => 'nullable|numeric|between:-90,90',
            'origin_lng' => 'nullable|numeric|between:-180,180',
            'destination_lat' => 'nullable|numeric|between:-90,90',
            'destination_lng' => 'nullable|numeric|between:-180,180',
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
        
        // Calcular costo basado en distancia si tenemos coordenadas
        if ($validated['origin_lat'] && $validated['origin_lng'] && $validated['destination_lat'] && $validated['destination_lng']) {
            $distance = $this->calculateDistance(
                $validated['origin_lat'],
                $validated['origin_lng'],
                $validated['destination_lat'],
                $validated['destination_lng']
            );
            $validated['distance_km'] = round($distance, 2);
            $validated['estimated_cost'] = round(($distance * 3) + 5, 2); // $3 por km + $5 base
        } else {
            $validated['estimated_cost'] = rand(10, 50); // Costo simulado sin coordenadas
        }

        $trip = Trip::create($validated);

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

        $trips = Trip::where('status', 'pending')->get();

        return view('trip.list-pending', ['user' => $user, 'trips' => $trips]);
    }

    // Aceptar viaje (Conductor)
    public function accept($id)
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

        $trip = Trip::find($id);

        if (!$trip || $trip->status !== 'pending') {
            return back()->with('error', 'Este viaje no está disponible.');
        }

        $trip->update([
            'driver_id' => $user['id'],
            'status' => 'accepted',
        ]);

        return redirect()->route('trip.show', $trip->id)->with('success', 'Viaje aceptado.');
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

        $trip = Trip::find($id);

        if (!$trip || $trip->driver_id !== $user['id'] || $trip->status !== 'accepted') {
            return back()->with('error', 'No puedes iniciar este viaje.');
        }

        $trip->update([
            'status' => 'in_progress',
            'start_time' => now(),
        ]);

        return redirect()->route('trip.show', $trip->id)->with('success', 'Viaje iniciado.');
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

        $trip = Trip::find($id);

        if (!$trip || $trip->driver_id !== $user['id'] || $trip->status !== 'in_progress') {
            return back()->with('error', 'No puedes completar este viaje.');
        }

        $trip->update([
            'status' => 'completed',
            'end_time' => now(),
            'final_cost' => $trip->estimated_cost,
        ]);

        return redirect()->route('trip.show', $trip->id)->with('success', 'Viaje completado.');
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
