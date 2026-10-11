<?php
namespace Tests\Feature;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class AdminAccountControlTest extends TestCase {
 use RefreshDatabase;
 private function user(string $role='pasajero',array $extra=[]): User {
  return User::factory()->create(array_merge(['role'=>$role,'status'=>'active','email_verified_at'=>now()],$extra));
 }
 public function test_admin_can_suspend_and_reactivate_an_account(): void {
  $admin=$this->user('admin'); $passenger=$this->user();
  $this->actingAs($admin)->patch(route('admin.users.suspend',$passenger),['reason'=>'Actividad sospechosa confirmada'])->assertRedirect();
  $passenger->refresh();
  $this->assertSame('suspended',$passenger->status);
  $this->assertSame($admin->id,$passenger->suspended_by);
  $this->assertDatabaseHas('user_notifications',['user_id'=>$passenger->id,'type'=>'account_suspended']);
  $this->actingAs($admin)->patch(route('admin.users.reactivate',$passenger))->assertRedirect();
  $this->assertSame('active',$passenger->fresh()->status);
 }
 public function test_suspended_account_cannot_login_or_use_business_routes(): void {
  $user=$this->user('pasajero',['password'=>bcrypt('secret123'),'status'=>'suspended','suspended_at'=>now(),'suspension_reason'=>'Revisión de seguridad']);
  $this->post(route('login.submit'),['email'=>$user->email,'password'=>'secret123'])->assertRedirect()->assertSessionHas('error');
  $this->assertGuest();
  $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'));
  $this->assertGuest();
 }
 public function test_admin_cannot_suspend_self_and_passenger_cannot_manage_users(): void {
  $admin=$this->user('admin'); $passenger=$this->user();
  $this->actingAs($admin)->patch(route('admin.users.suspend',$admin),['reason'=>'Intento no permitido'])->assertStatus(422);
  $this->assertSame('active',$admin->fresh()->status);
  $this->actingAs($passenger)->get(route('admin.users.index'))->assertForbidden();
 }
 public function test_audit_view_is_admin_only_and_supports_filters(): void {
  $admin=$this->user('admin'); $passenger=$this->user();
  AuditLog::create(['user_id'=>$admin->id,'event'=>'admin.users.suspend','route_name'=>'admin.users.suspend','method'=>'PATCH','path'=>'admin/usuarios/2/suspender','response_status'=>302]);
  $this->actingAs($admin)->get(route('admin.audit.index',['user_id'=>$admin->id,'method'=>'PATCH']))->assertOk()->assertSee('admin.users.suspend');
  $this->actingAs($passenger)->get(route('admin.audit.index'))->assertForbidden();
 }
}
