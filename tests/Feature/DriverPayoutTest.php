<?php
namespace Tests\Feature;
use App\Models\DriverEarning;
use App\Models\DriverProfile;
use App\Models\PayoutRequest;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class DriverPayoutTest extends TestCase
{
 use RefreshDatabase;
 public function test_account_is_encrypted_and_available_balance_is_reserved_once(): void
 {
  $driver=$this->driver();$this->actingAs($driver)->post(route('payouts.account'),['holder_name'=>'Conductor Uno','holder_document'=>'123456789','bank_name'=>'Banco Demo','account_type'=>'ahorros','account_number'=>'9876543210'])->assertRedirect();
  $raw=DB::table('payout_accounts')->where('driver_id',$driver->id)->first();$this->assertNotSame('9876543210',$raw->account_number);$this->assertSame('3210',$raw->account_last_four);
  $this->earning($driver,12000,1);$this->earning($driver,10000,2);
  $this->actingAs($driver)->post(route('payouts.request'))->assertRedirect();
  $request=PayoutRequest::first();$this->assertEquals(22000,$request->amount);$this->assertSame(2,DriverEarning::where('payout_request_id',$request->id)->count());
  $this->actingAs($driver)->post(route('payouts.request'))->assertStatus(422);$this->assertDatabaseCount('payout_requests',1);
 }
 public function test_admin_approval_and_payment_marks_reserved_earnings_paid(): void
 {
  $driver=$this->driver();$account=\App\Models\PayoutAccount::create(['driver_id'=>$driver->id,'holder_name'=>'Conductor','holder_document'=>'123','bank_name'=>'Banco','account_type'=>'ahorros','account_number'=>'12345678','account_last_four'=>'5678']);$earning=$this->earning($driver,25000,1);$p=PayoutRequest::create(['driver_id'=>$driver->id,'payout_account_id'=>$account->id,'amount'=>25000,'status'=>'pending']);$earning->update(['payout_request_id'=>$p->id]);$admin=$this->user('admin','payadmin@example.com');
  $this->actingAs($admin)->patch(route('admin.payouts.update',$p),['status'=>'approved'])->assertRedirect();$this->assertSame('approved',$p->fresh()->status);
  $this->actingAs($admin)->patch(route('admin.payouts.update',$p),['status'=>'paid','payment_reference'=>'TX-123'])->assertRedirect();$this->assertSame('paid',$p->fresh()->status);$this->assertSame('paid',$earning->fresh()->status);$this->assertDatabaseHas('user_notifications',['user_id'=>$driver->id,'type'=>'payout']);
 }
 public function test_rejected_request_releases_earnings(): void
 {
  $driver=$this->driver();$account=\App\Models\PayoutAccount::create(['driver_id'=>$driver->id,'holder_name'=>'Conductor','holder_document'=>'123','bank_name'=>'Banco','account_type'=>'ahorros','account_number'=>'12345678','account_last_four'=>'5678']);$earning=$this->earning($driver,25000,1);$p=PayoutRequest::create(['driver_id'=>$driver->id,'payout_account_id'=>$account->id,'amount'=>25000,'status'=>'pending']);$earning->update(['payout_request_id'=>$p->id]);$admin=$this->user('admin','rejectadmin@example.com');$this->actingAs($admin)->patch(route('admin.payouts.update',$p),['status'=>'rejected','review_notes'=>'Datos incorrectos'])->assertRedirect();$this->assertNull($earning->fresh()->payout_request_id);$this->assertSame('available',$earning->fresh()->status);
 }
 private function earning(User $driver,float $net,int $suffix): DriverEarning{$passenger=$this->user('pasajero','p'.$suffix.uniqid().'@example.com');$trip=Trip::create(['passenger_id'=>$passenger->id,'driver_id'=>$driver->id,'vehicle_type'=>'carro','department'=>'Antioquia','municipality'=>'Medellin','origin'=>'O','destination'=>'D','status'=>'completed','estimated_cost'=>$net,'final_cost'=>$net]);return DriverEarning::create(['trip_id'=>$trip->id,'driver_id'=>$driver->id,'gross_amount'=>$net,'commission_rate'=>0,'platform_fee'=>0,'net_amount'=>$net,'status'=>'available','available_at'=>now()]);}
 private function driver():User{$d=$this->user('conductor','driver'.uniqid().'@example.com');DriverProfile::create(['user_id'=>$d->id,'document_number'=>'D'.uniqid(),'license_number'=>'L'.uniqid(),'vehicle_plate'=>strtoupper(substr(uniqid(),-6)),'vehicle_type'=>'auto','verification_status'=>'approved','status'=>'active']);return $d;}
 private function user(string $role,string $email):User{return User::create(['name'=>ucfirst($role),'email'=>$email,'password'=>Hash::make('password123'),'role'=>$role,'email_verified_at'=>now()]);}
}