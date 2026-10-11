<?php

namespace Tests\Feature;

use App\Models\DriverProfile;
use App\Models\Trip;
use App\Models\User;
use App\Services\RoutePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PricingAndTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_server_quote_uses_route_distance_and_auditable_breakdown(): void
    {
        Http::fake([
            '*' => Http::response([
                'routes' => [[
                    'distance' => 10000,
                    'duration' => 1200,
                ]],
            ]),
        ]);

        $quote = app(RoutePricingService::class)->quote('carro', 6.24, -75.58, 6.30, -75.50);

        $this->assertSame(10.0, $quote['distance_km']);
        $this->assertSame(20, $quote['estimated_duration_minutes']);
        $this->assertSame(4000.0, $quote['base_fare']);
        $this->assertSame(12000.0, $quote['distance_fare']);
        $this->assertSame(3000.0, $quote['time_fare']);
        $this->assertSame(800.0, $quote['booking_fee']);
        $this->assertSame(19800.0, $quote['estimated_cost']);
        $this->assertSame('COP', $quote['currency']);
        $this->assertSame('osrm', $quote['route_source']);
    }

    public function test_quote_has_deterministic_fallback_when_router_is_down(): void
    {
        Http::fake(['*' => Http::response([], 503)]);

        $quote = app(RoutePricingService::class)->quote('moto', 6.24, -75.58, 6.25, -75.57);

        $this->assertSame('fallback', $quote['route_source']);
        $this->assertGreaterThan(0, $quote['distance_km']);
        $this->assertGreaterThanOrEqual(4500, $quote['estimated_cost']);
    }

    public function test_only_trip_participants_can_read_live_driver_location(): void
    {
        $passenger = $this->user('pasajero', 'passenger@example.com');
        $outsider = $this->user('pasajero', 'outsider@example.com');
        $driver = $this->user('conductor', 'driver@example.com');
        DriverProfile::create([
            'user_id' => $driver->id,
            'document_number' => 'DOC-TRACK',
            'license_number' => 'LIC-TRACK',
            'vehicle_plate' => 'TRK123',
            'vehicle_type' => 'auto',
            'vehicle_make' => 'Honda',
            'vehicle_model' => 'Wave',
            'vehicle_color' => 'Negra',
            'verification_status' => 'approved',
            'status' => 'active',
            'is_online' => true,
            'current_lat' => 6.2442,
            'current_lng' => -75.5812,
            'location_updated_at' => now(),
            'location_accuracy_m' => 8.5,
            'location_heading' => 120,
            'location_speed_mps' => 7.2,
        ]);
        $trip = Trip::create([
            'passenger_id' => $passenger->id,
            'driver_id' => $driver->id,
            'vehicle_type' => 'carro',
            'department' => 'Antioquia',
            'municipality' => 'Medellin',
            'origin' => 'Origen',
            'destination' => 'Destino',
            'status' => 'accepted',
            'pickup_pin' => '1234',
            'estimated_cost' => 15000,
        ]);

        $this->actingAs($passenger)
            ->getJson(route('trip.live-status', $trip))
            ->assertOk()
            ->assertJsonPath('driver.name', $driver->name)
            ->assertJsonPath('driver.latitude', 6.2442)
            ->assertJsonPath('driver.location_fresh', true)
            ->assertJsonPath('driver.accuracy', 8.5)
            ->assertJsonPath('driver.vehicle.plate', 'TRK123')
            ->assertJsonPath('driver.vehicle.make', 'Honda')
            ->assertJsonPath('driver.trip_stage', 'to_pickup')
            ->assertJsonPath('driver.target_label', 'Punto de recogida');

        $trip->update(['status' => 'in_progress']);
        $this->actingAs($passenger)->getJson(route('trip.live-status', $trip))
            ->assertOk()
            ->assertJsonPath('driver.trip_stage', 'to_destination')
            ->assertJsonPath('driver.target_label', 'Destino')
            ->assertJsonPath('driver.distance_to_pickup_km', null);

        $this->actingAs($passenger)
            ->withSession(['user' => [
                'id' => $passenger->id,
                'name' => $passenger->name,
                'email' => $passenger->email,
                'role' => 'pasajero',
            ]])
            ->get(route('trip.show', $trip))
            ->assertOk()
            ->assertSee('Seguimiento del vehículo')
            ->assertSee('const tripData =', false);

        $this->actingAs($driver)
            ->withSession(['user' => ['id' => $driver->id, 'name' => $driver->name, 'email' => $driver->email, 'role' => 'conductor']])
            ->get(route('trip.show', $trip))
            ->assertOk()
            ->assertSee('navigator.geolocation.watchPosition', false)
            ->assertSee('const driverLocationUrl =', false);

        $this->actingAs($outsider)
            ->getJson(route('trip.live-status', $trip))
            ->assertForbidden();
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => Hash::make('password123'),
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }
}