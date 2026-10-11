<?php
namespace Tests\Feature;
use App\Models\DriverEarning;
use App\Models\DriverProfile;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class EarningsRatingsNotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_trip_creates_pending_net_earning_and_notification(): void
    {
        [$passenger,$driver,$trip] = $this->scenario('in_progress');
        $this->actingAs($driver)->post(route('trip.complete',$trip))->assertRedirect(route('trip.show',$trip));
        $this->assertDatabaseHas('driver_earnings',['trip_id'=>$trip->id,'gross_amount'=>20000,'platform_fee'=>2400,'net_amount'=>17600,'status'=>'pending']);
        $this->assertDatabaseHas('user_notifications',['user_id'=>$passenger->id,'type'=>'trip_completed']);
    }

    public function test_admin_reconciliation_releases_driver_balance(): void
    {
        [$passenger,$driver,$trip] = $this->scenario('completed');
        $earning=DriverEarning::create(['trip_id'=>$trip->id,'driver_id'=>$driver->id,'gross_amount'=>20000,'commission_rate'=>12,'platform_fee'=>2400,'net_amount'=>17600,'status'=>'pending']);
        $payment=Payment::create(['trip_id'=>$trip->id,'user_id'=>$passenger->id,'amount'=>20000,'status'=>'pending']);
        $admin=$this->user('admin','admin2@example.com');
        $this->actingAs($admin)->post(route('payment.reconcile',$payment))->assertRedirect();
        $this->assertSame('completed',$payment->fresh()->status);
        $this->assertSame('available',$earning->fresh()->status);
        $this->assertNotNull($earning->fresh()->available_at);
    }

    public function test_passenger_rates_driver_once_and_updates_average(): void
    {
        [$passenger,$driver,$trip] = $this->scenario('completed');
        $this->actingAs($passenger)->post(route('trip.rating.store',$trip),['rating'=>4,'comment'=>'Buen servicio'])->assertRedirect();
        $this->assertDatabaseHas('trip_ratings',['trip_id'=>$trip->id,'reviewer_id'=>$passenger->id,'reviewee_id'=>$driver->id,'rating'=>4]);
        $this->assertEquals(4.0, $driver->driverProfile->fresh()->rating);
        $this->assertDatabaseHas('user_notifications',['user_id'=>$driver->id,'type'=>'rating']);
        $this->actingAs($passenger)->from(route('trip.show',$trip))->post(route('trip.rating.store',$trip),['rating'=>5])->assertSessionHasErrors('rating');
        $this->assertDatabaseCount('trip_ratings',1);
    }

    private function scenario(string $status): array
    {
        $passenger=$this->user('pasajero','p'.uniqid().'@example.com');
        $driver=$this->user('conductor','d'.uniqid().'@example.com');
        DriverProfile::create(['user_id'=>$driver->id,'document_number'=>'D'.uniqid(),'license_number'=>'L'.uniqid(),'vehicle_plate'=>strtoupper(substr(uniqid(),-6)),'vehicle_type'=>'auto','verification_status'=>'approved','status'=>'active']);
        $trip=Trip::create(['passenger_id'=>$passenger->id,'driver_id'=>$driver->id,'vehicle_type'=>'carro','department'=>'Antioquia','municipality'=>'Medellin','origin'=>'Origen','destination'=>'Destino','status'=>$status,'pickup_pin'=>'1234','estimated_cost'=>20000,'final_cost'=>$status==='completed'?20000:null]);
        return [$passenger,$driver,$trip];
    }
    private function user(string $role,string $email): User
    { return User::create(['name'=>ucfirst($role),'email'=>$email,'password'=>Hash::make('password123'),'role'=>$role,'email_verified_at'=>now()]); }
}