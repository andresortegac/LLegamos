<?php
namespace Tests\Feature;
use App\Models\DriverProfile;
use App\Models\Trip;
use App\Models\TripIncident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class TripSafetyTest extends TestCase
{
 use RefreshDatabase;
 public function test_participant_creates_expiring_public_share_link(): void
 {
  [$passenger,$driver,$trip]=$this->scenario();
  $response=$this->actingAs($passenger)->post(route('trip.share',$trip));
  $response->assertSessionHas('share_url');
  $url=$response->getSession()->get('share_url');
  $this->getJson($url)->assertOk()->assertJsonPath('status','accepted')->assertJsonPath('driver',$driver->name);
  $outsider=$this->user('pasajero','outside@example.com');
  $this->actingAs($outsider)->post(route('trip.share',$trip))->assertForbidden();
 }
 public function test_sos_creates_single_critical_incident_and_notifies_admin(): void
 {
  [$passenger,$driver,$trip]=$this->scenario();$admin=$this->user('admin','sos-admin@example.com');
  $this->actingAs($passenger)->post(route('trip.sos',$trip),['latitude'=>6.24,'longitude'=>-75.58])->assertRedirect();
  $this->assertDatabaseHas('trip_incidents',['trip_id'=>$trip->id,'reported_by'=>$passenger->id,'type'=>'sos','severity'=>'critical','status'=>'open']);
  $this->assertDatabaseHas('user_notifications',['user_id'=>$admin->id,'type'=>'sos']);
  $this->actingAs($passenger)->post(route('trip.sos',$trip));
  $this->assertDatabaseCount('trip_incidents',1);
 }
 public function test_admin_can_resolve_incident(): void
 {
  [$passenger,,$trip]=$this->scenario();$admin=$this->user('admin','resolver@example.com');
  $incident=TripIncident::create(['trip_id'=>$trip->id,'reported_by'=>$passenger->id,'type'=>'safety','severity'=>'high','description'=>'Situacion de seguridad que requiere revision.']);
  $this->actingAs($admin)->patch(route('admin.incidents.update',$incident),['status'=>'resolved','resolution_notes'=>'Caso revisado y cerrado.'])->assertRedirect();
  $incident->refresh();$this->assertSame('resolved',$incident->status);$this->assertSame($admin->id,$incident->assigned_admin_id);$this->assertNotNull($incident->resolved_at);
 }
 public function test_emergency_contacts_are_private_to_owner(): void
 {
  $owner=$this->user('pasajero','owner@example.com');$other=$this->user('pasajero','other@example.com');
  $this->actingAs($owner)->post(route('safety.contacts.store'),['name'=>'Familiar','phone'=>'3001234567','relationship'=>'Hermana']);
  $id=\App\Models\EmergencyContact::where('user_id',$owner->id)->value('id');
  $this->actingAs($other)->delete(route('safety.contacts.destroy',$id))->assertNotFound();
  $this->assertDatabaseHas('emergency_contacts',['id'=>$id,'user_id'=>$owner->id]);
 }
 public function test_monitor_creates_alert_for_stale_driver_signal(): void
 {
  [$passenger,$driver,$trip]=$this->scenario();$admin=$this->user('admin','monitor@example.com');
  $driver->driverProfile->update(['location_updated_at'=>now()->subMinutes(3)]);
  app(\App\Services\TripSafetyMonitor::class)->inspectStaleTrips();
  $this->assertDatabaseHas('trip_incidents',['trip_id'=>$trip->id,'type'=>'safety','severity'=>'high','status'=>'open']);
  $this->assertDatabaseHas('user_notifications',['user_id'=>$passenger->id,'type'=>'safety_alert']);
  $this->assertDatabaseHas('user_notifications',['user_id'=>$admin->id,'type'=>'safety_alert']);
  app(\App\Services\TripSafetyMonitor::class)->inspectStaleTrips();
  $this->assertSame(1,TripIncident::where('trip_id',$trip->id)->where('type','safety')->count());
 } private function scenario(): array
 { $p=$this->user('pasajero','p'.uniqid().'@example.com');$d=$this->user('conductor','d'.uniqid().'@example.com');DriverProfile::create(['user_id'=>$d->id,'document_number'=>'D'.uniqid(),'license_number'=>'L'.uniqid(),'vehicle_plate'=>strtoupper(substr(uniqid(),-6)),'vehicle_type'=>'auto','verification_status'=>'approved','status'=>'active','is_online'=>true,'current_lat'=>6.24,'current_lng'=>-75.58,'location_updated_at'=>now()]);$t=Trip::create(['passenger_id'=>$p->id,'driver_id'=>$d->id,'vehicle_type'=>'carro','department'=>'Antioquia','municipality'=>'Medellin','origin'=>'Origen','destination'=>'Destino','status'=>'accepted','pickup_pin'=>'1234','estimated_cost'=>15000]);return[$p,$d,$t]; }
 private function user(string $role,string $email): User{return User::create(['name'=>ucfirst($role),'email'=>$email,'password'=>Hash::make('password123'),'role'=>$role,'email_verified_at'=>now()]);}
}