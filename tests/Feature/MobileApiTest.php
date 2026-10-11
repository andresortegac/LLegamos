<?php

namespace Tests\Feature;

use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MobileApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_active_user_can_create_and_revoke_a_device_token(): void
    {
        $user = $this->user('pasajero');
        $login = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
            'device_name' => 'android-test',
        ])->assertOk()->assertJsonPath('token_type', 'Bearer')->assertJsonPath('user.role', 'pasajero');

        $token = $login->json('token');
        $this->withToken($token)->getJson('/api/v1/me')->assertOk()->assertJsonPath('user.id', $user->id);
        $this->withToken($token)->deleteJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_unverified_or_suspended_accounts_cannot_create_mobile_tokens(): void
    {
        $unverified = $this->user('pasajero', false);
        $this->postJson('/api/v1/auth/login', [
            'email' => $unverified->email,
            'password' => 'password123',
            'device_name' => 'phone',
        ])->assertForbidden();

        $suspended = $this->user('pasajero');
        $suspended->update(['status' => 'suspended']);
        $this->postJson('/api/v1/auth/login', [
            'email' => $suspended->email,
            'password' => 'password123',
            'device_name' => 'phone',
        ])->assertForbidden();
    }

    public function test_only_approved_online_driver_token_can_publish_location(): void
    {
        $passenger = $this->user('pasajero');
        $passengerToken = $passenger->createToken('phone', ['profile:read'])->plainTextToken;
        $this->withToken($passengerToken)->postJson('/api/v1/driver/location', [
            'latitude' => 0.502,
            'longitude' => -76.497,
        ])->assertForbidden();
        $this->app['auth']->forgetGuards();

        $driver = $this->user('conductor');
        DriverProfile::create([
            'user_id' => $driver->id,
            'document_number' => 'API-DOC',
            'license_number' => 'API-LIC',
            'vehicle_plate' => 'API123',
            'vehicle_type' => 'moto',
            'verification_status' => 'approved',
            'status' => 'active',
            'is_online' => true,
        ]);
        $driverToken = $driver->createToken('android-driver', ['profile:read', 'driver:location'])->plainTextToken;
        $this->withToken($driverToken)->postJson('/api/v1/driver/location', [
            'latitude' => 0.502,
            'longitude' => -76.497,
            'accuracy' => 6.5,
            'heading' => 90,
            'speed' => 4.2,
        ])->assertOk()->assertJsonStructure(['updated_at']);

        $profile = $driver->driverProfile->fresh();
        $this->assertSame('0.50200000', $profile->current_lat);
        $this->assertSame('6.50', $profile->location_accuracy_m);
    }

    private function user(string $role, bool $verified = true): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => uniqid($role, true).'@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
            'status' => 'active',
            'email_verified_at' => $verified ? now() : null,
        ]);
    }
}