<?php

namespace Tests\Feature;

use App\Models\DriverProfile;
use App\Models\Trip;
use App\Models\TripOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileDriverFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_driver_can_operate_the_trip_lifecycle_from_mobile_api(): void
    {
        $passenger = $this->user('pasajero', 'mobile-passenger@example.com');
        $driver = $this->user('conductor', 'mobile-driver@example.com');
        DriverProfile::create([
            'user_id' => $driver->id, 'document_number' => 'MOB-DOC', 'license_number' => 'MOB-LIC',
            'vehicle_plate' => 'MOB123', 'vehicle_type' => 'moto', 'verification_status' => 'approved',
            'status' => 'active', 'is_online' => true, 'current_lat' => 0.502, 'current_lng' => -76.497,
            'location_updated_at' => now(),
        ]);
        $trip = Trip::create([
            'passenger_id' => $passenger->id, 'vehicle_type' => 'moto', 'department' => 'Putumayo',
            'municipality' => 'Puerto Asís', 'origin' => 'Parque principal', 'destination' => 'Hospital',
            'origin_lat' => 0.502, 'origin_lng' => -76.497, 'destination_lat' => 0.51, 'destination_lng' => -76.50,
            'status' => 'pending', 'pickup_pin' => '4821', 'estimated_cost' => 8500, 'currency' => 'COP',
        ]);
        TripOffer::create(['trip_id' => $trip->id, 'driver_id' => $driver->id, 'distance_km' => 0.8, 'status' => 'pending', 'expires_at' => now()->addMinutes(2)]);
        $token = $driver->createToken('android', ['profile:read', 'driver:location', 'driver:trips'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/driver/home')->assertOk()
            ->assertJsonPath('driver.is_online', true)->assertJsonPath('offers.0.trip.id', $trip->id);
        $this->withToken($token)->postJson("/api/v1/driver/trips/{$trip->id}/accept")->assertOk()
            ->assertJsonPath('trip.status', 'accepted');
        $this->withToken($token)->postJson("/api/v1/driver/trips/{$trip->id}/start", ['pickup_pin' => '0000'])->assertUnprocessable();
        $this->withToken($token)->postJson("/api/v1/driver/trips/{$trip->id}/start", ['pickup_pin' => '4821'])->assertOk()
            ->assertJsonPath('trip.status', 'in_progress');
        $this->withToken($token)->postJson("/api/v1/driver/trips/{$trip->id}/complete")->assertOk()
            ->assertJsonPath('trip.status', 'completed');

        $this->assertDatabaseHas('trips', ['id' => $trip->id, 'driver_id' => $driver->id, 'status' => 'completed']);
        $this->assertDatabaseHas('driver_earnings', ['trip_id' => $trip->id, 'driver_id' => $driver->id]);
    }

    private function user(string $role, string $email): User
    {
        return User::create(['name' => ucfirst($role), 'email' => $email, 'password' => Hash::make('password123'), 'role' => $role, 'status' => 'active', 'email_verified_at' => now()]);
    }
}