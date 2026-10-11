<?php
namespace Tests\Feature;
use App\Models\DriverProfile;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;
class AccountSecurityTest extends TestCase
{
 use RefreshDatabase;
 public function test_unverified_user_is_redirected_and_can_request_verification_email(): void
 {
  Notification::fake();$user=$this->user(false);
  $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
  $this->actingAs($user)->post(route('verification.send'))->assertRedirect();
  Notification::assertSentTo($user,VerifyEmail::class);
 }
 public function test_verified_user_can_open_dashboard(): void
 {
  $user=$this->user(true);$this->actingAs($user)->get(route('dashboard'))->assertOk();
 }
 public function test_password_reset_link_is_sent_without_exposing_account_data(): void
 {
  Notification::fake();$user=$this->user(true);
  $message='Si el correo esta registrado, te enviamos un enlace para cambiar la contrasena. Revisa la bandeja de entrada y correo no deseado.';
  $this->post(route('password.email'),['email'=>$user->email])->assertSessionHas('status',$message)->assertSessionHas('success',$message);
  Notification::assertSentTo($user,ResetPassword::class);
 }
 public function test_password_change_redirects_with_visible_confirmation(): void
 {
  $user=$this->user(true);$token=Password::broker()->createToken($user);
  $this->post(route('password.update'),['token'=>$token,'email'=>$user->email,'password'=>'NuevaClave123','password_confirmation'=>'NuevaClave123'])
   ->assertRedirect(route('login'))
   ->assertSessionHas('success','Tu contrasena fue cambiada correctamente. Ya puedes iniciar sesion.');
  $this->assertTrue(Hash::check('NuevaClave123',$user->fresh()->password));
 }
 public function test_mutating_authenticated_request_is_audited_without_payload(): void
 {
  $user=$this->user(true,'conductor');DriverProfile::create(['user_id'=>$user->id,'document_number'=>'AUD-DOC','license_number'=>'AUD-LIC','vehicle_plate'=>'AUD123','vehicle_type'=>'auto','verification_status'=>'approved','status'=>'active']);
  $this->actingAs($user)->post(route('driver.availability'),['is_online'=>1,'latitude'=>6.24,'longitude'=>-75.58])->assertRedirect();
  $this->assertDatabaseHas('audit_logs',['user_id'=>$user->id,'event'=>'driver.availability','method'=>'POST','response_status'=>302]);
  $log=\App\Models\AuditLog::where('event','driver.availability')->first();$this->assertArrayNotHasKey('latitude',$log->context);$this->assertArrayNotHasKey('longitude',$log->context);
 }
 private function user(bool $verified,string $role='pasajero'):User{return User::create(['name'=>'Cuenta Segura','email'=>$role.uniqid().'@example.com','password'=>Hash::make('password123'),'role'=>$role,'email_verified_at'=>$verified?now():null]);}
}