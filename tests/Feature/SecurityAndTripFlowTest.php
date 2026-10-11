<?php

namespace Tests\Feature;

use App\Models\DriverProfile;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\TripOffer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SecurityAndTripFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_cannot_create_an_admin(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
    }

    public function test_passenger_cannot_open_admin_dashboard(): void
    {
        $passenger = $this->user('pasajero');

        $this->actingAs($passenger)
            ->get(route('dashboard.admin'))
            ->assertForbidden();
    }

    public function test_guest_is_redirected_from_private_routes(): void
    {
        $this->get(route('dashboard.admin'))->assertRedirect(route('login'));
        $this->get(route('trip.list-pending'))->assertRedirect(route('login'));
    }

    public function test_only_one_approved_driver_can_accept_a_pending_trip(): void
    {
        $passenger = $this->user('pasajero');
        $firstDriver = $this->approvedDriver('AAA111');
        $secondDriver = $this->approvedDriver('BBB222');
        $trip = Trip::create([
            'passenger_id' => $passenger->id,
            'vehicle_type' => 'carro',
            'department' => 'Antioquia',
            'municipality' => 'Medellin',
            'origin' => 'Origen',
            'origin_lat' => 6.2442,
            'origin_lng' => -75.5812,
            'destination' => 'Destino',
            'status' => 'pending',
            'estimated_cost' => 20000,
        ]);

        TripOffer::create(['trip_id'=>$trip->id,'driver_id'=>$firstDriver->id,'distance_km'=>1.2,'status'=>'pending','expires_at'=>now()->addMinute()]);
        TripOffer::create(['trip_id'=>$trip->id,'driver_id'=>$secondDriver->id,'distance_km'=>1.5,'status'=>'pending','expires_at'=>now()->addMinute()]);

        $this->actingAs($firstDriver)
            ->post(route('trip.accept', $trip))
            ->assertRedirect(route('trip.show', $trip));

        $this->actingAs($secondDriver)
            ->from(route('trip.list-pending'))
            ->post(route('trip.accept', $trip))
            ->assertNotFound();

        $trip->refresh();
        $this->assertSame($firstDriver->id, $trip->driver_id);
        $this->assertSame('accepted', $trip->status);
    }

    public function test_driver_document_is_private_to_owner_and_admin(): void
    {
        config(['filesystems.private' => 's3']);
        Storage::fake('s3');
        $owner = $this->approvedDriver('CCC333');
        $other = $this->approvedDriver('DDD444');
        $profile = $owner->driverProfile;
        $profile->update(['license_document_path' => 'drivers/documents/license/test.pdf']);
        Storage::disk('s3')->put($profile->license_document_path, 'private-document');

        $this->actingAs($other)
            ->get(route('driver-profile.document', [$profile->id, 'license']))
            ->assertForbidden();

        $this->actingAs($owner)
            ->get(route('driver-profile.document', [$profile->id, 'license']))
            ->assertOk();
    }

    public function test_passenger_location_control_saves_coordinates_without_requiring_map(): void
    {
        $passenger = $this->user('pasajero');

        $this->actingAs($passenger)
            ->get(route('trip.create-request'))
            ->assertOk()
            ->assertSee('id="location-status"', false)
            ->assertSee("document.getElementById('origin_lat').value = lat.toFixed(6);", false)
            ->assertSee('Permiso de ubicacion bloqueado', false);
    }
    public function test_cash_payment_stays_pending_and_card_is_rejected(): void
    {
        $passenger = $this->user('pasajero');
        $trip = Trip::create([
            'passenger_id' => $passenger->id,
            'vehicle_type' => 'carro',
            'department' => 'Antioquia',
            'municipality' => 'Medellin',
            'origin' => 'Origen',
            'origin_lat' => 6.2442,
            'origin_lng' => -75.5812,
            'destination' => 'Destino',
            'status' => 'completed',
            'estimated_cost' => 20000,
            'final_cost' => 22000,
        ]);

        $this->actingAs($passenger)
            ->post(route('payment.process', $trip), ['payment_method' => 'cash'])
            ->assertRedirect(route('trip.show', $trip));

        $this->assertDatabaseHas('payments', [
            'trip_id' => $trip->id,
            'status' => 'pending',
            'amount' => 22000,
        ]);

        Payment::query()->delete();

        $this->actingAs($passenger)
            ->post(route('payment.process', $trip), ['payment_method' => 'card'])
            ->assertSessionHasErrors('payment_method');

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_trip_requires_correct_pin_to_start(): void
    {
        $passenger = $this->user('pasajero');
        $driver = $this->approvedDriver('EEE555');
        $trip = Trip::create([
            'passenger_id' => $passenger->id,
            'driver_id' => $driver->id,
            'vehicle_type' => 'carro',
            'department' => 'Antioquia',
            'municipality' => 'Medellin',
            'origin' => 'Origen',
            'destination' => 'Destino',
            'status' => 'accepted',
            'pickup_pin' => '4321',
            'estimated_cost' => 20000,
        ]);

        $this->actingAs($driver)
            ->from(route('trip.show', $trip))
            ->post(route('trip.start', $trip), ['pickup_pin' => '1111'])
            ->assertSessionHasErrors('pickup_pin');
        $this->assertSame('accepted', $trip->fresh()->status);

        $this->actingAs($driver)
            ->post(route('trip.start', $trip), ['pickup_pin' => '4321'])
            ->assertRedirect(route('trip.show', $trip));
        $this->assertSame('in_progress', $trip->fresh()->status);
    }

    public function test_passenger_can_cancel_pending_trip_with_reason(): void
    {
        $passenger = $this->user('pasajero');
        $trip = Trip::create([
            'passenger_id' => $passenger->id,
            'vehicle_type' => 'carro',
            'department' => 'Antioquia',
            'municipality' => 'Medellin',
            'origin' => 'Origen',
            'destination' => 'Destino',
            'status' => 'pending',
            'pickup_pin' => '9876',
            'estimated_cost' => 20000,
        ]);

        $this->actingAs($passenger)
            ->post(route('trip.cancel', $trip), ['reason' => 'Cambie de planes'])
            ->assertRedirect(route('dashboard'));

        $trip->refresh();
        $this->assertSame('cancelled', $trip->status);
        $this->assertSame('pasajero', $trip->cancelled_by);
        $this->assertSame('Cambie de planes', $trip->cancellation_reason);
        $this->assertNotNull($trip->cancelled_at);
    }

    public function test_approved_driver_can_toggle_availability_with_location(): void
    {
        $driver = $this->approvedDriver('FFF666');
        $driver->driverProfile->update(['is_online' => false]);

        $this->actingAs($driver)
            ->post(route('driver.availability'), [
                'is_online' => 1,
                'latitude' => 6.25,
                'longitude' => -75.58,
            ])
            ->assertRedirect();

        $profile = $driver->driverProfile->fresh();
        $this->assertTrue($profile->is_online);
        $this->assertSame('6.25000000', $profile->current_lat);
        $this->assertNotNull($profile->location_updated_at);
    }
    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => uniqid($role, true) . '@example.com',
            'password' => Hash::make('password123'),
            'role' => $role,
            'email_verified_at' => now(),
        ]);
    }

    private function approvedDriver(string $plate): User
    {
        $driver = $this->user('conductor');

        DriverProfile::create([
            'user_id' => $driver->id,
            'document_number' => 'DOC' . $plate,
            'license_number' => 'LIC' . $plate,
            'vehicle_plate' => $plate,
            'vehicle_type' => 'auto',
            'status' => 'active',
            'verification_status' => 'approved',
            'background_check_passed' => true,
            'is_online' => true,
            'current_lat' => 6.2450,
            'current_lng' => -75.5800,
            'location_updated_at' => now(),
        ]);

        return $driver->fresh('driverProfile');
    }
}