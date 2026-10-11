<?php
namespace Tests\Feature;
use App\Models\DriverProfile;
use App\Models\Trip;
use App\Models\TripOffer;
use App\Models\User;
use App\Services\DispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class DispatchServiceTest extends TestCase
{
 use RefreshDatabase;
 public function test_nearest_eligible_driver_receives_exclusive_offer(): void
 {
  $passenger=$this->user('pasajero','passenger@example.com');
  $near=$this->driver('near@example.com','NEA111',6.245,-75.580);
  $this->driver('far@example.com','FAR222',6.50,-75.90);
  $trip=$this->trip($passenger);
  $offer=app(DispatchService::class)->dispatch($trip);
  $this->assertNotNull($offer);$this->assertSame($near->id,$offer->driver_id);$this->assertTrue($offer->expires_at->isFuture());
  $this->assertDatabaseCount('trip_offers',1);$this->assertDatabaseHas('user_notifications',['user_id'=>$near->id,'type'=>'trip_offer']);
 }
 public function test_rejection_immediately_offers_trip_to_next_driver(): void
 {
  $passenger=$this->user('pasajero','passenger2@example.com');$first=$this->driver('first@example.com','FIR111',6.245,-75.580);$second=$this->driver('second@example.com','SEC222',6.250,-75.585);$trip=$this->trip($passenger);$service=app(DispatchService::class);$offer=$service->dispatch($trip);$this->assertSame($first->id,$offer->driver_id);$service->reject($offer,$first->id);$next=TripOffer::where('trip_id',$trip->id)->where('status','pending')->first();$this->assertNotNull($next);$this->assertSame($second->id,$next->driver_id);$this->assertDatabaseHas('trip_offers',['id'=>$offer->id,'status'=>'rejected']);
 }
 public function test_acceptance_atomically_assigns_trip_and_expires_other_offers(): void
 {
  $passenger=$this->user('pasajero','passenger3@example.com');$driver=$this->driver('accept@example.com','ACC111',6.245,-75.580);$trip=$this->trip($passenger);$offer=app(DispatchService::class)->dispatch($trip);$assigned=app(DispatchService::class)->accept($offer,$driver->id);$this->assertSame('accepted',$assigned->status);$this->assertSame($driver->id,$assigned->driver_id);$this->assertDatabaseHas('trip_offers',['id'=>$offer->id,'status'=>'accepted']);$this->assertDatabaseHas('user_notifications',['user_id'=>$passenger->id,'type'=>'trip_accepted']);
 }
 private function user(string $role,string $email): User{return User::create(['name'=>$role,'email'=>$email,'password'=>Hash::make('password123'),'role'=>$role,'email_verified_at'=>now()]);}
 private function driver(string $email,string $plate,float $lat,float $lng):User{$u=$this->user('conductor',$email);DriverProfile::create(['user_id'=>$u->id,'document_number'=>'D'.$plate,'license_number'=>'L'.$plate,'vehicle_plate'=>$plate,'vehicle_type'=>'auto','status'=>'active','verification_status'=>'approved','is_online'=>true,'current_lat'=>$lat,'current_lng'=>$lng,'location_updated_at'=>now(),'rating'=>5]);return $u;}
 private function trip(User $passenger):Trip{return Trip::create(['passenger_id'=>$passenger->id,'vehicle_type'=>'carro','department'=>'Antioquia','municipality'=>'Medellin','origin'=>'Origen','origin_lat'=>6.2442,'origin_lng'=>-75.5812,'destination'=>'Destino','destination_lat'=>6.30,'destination_lng'=>-75.60,'status'=>'pending','estimated_cost'=>20000]);}
}
