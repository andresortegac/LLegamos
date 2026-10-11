<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class AdminUserController extends Controller {
 public function index(Request $request) {
  $filters=$request->validate(['search'=>'nullable|string|max:100','role'=>'nullable|in:pasajero,conductor,admin','status'=>'nullable|in:active,suspended']);
  $users=User::query()->with('driverProfile')
   ->when($filters['search']??null,function($query,$search){$query->where(function($nested)use($search){$nested->where('name','like',"%{$search}%")->orWhere('email','like',"%{$search}%");});})
   ->when($filters['role']??null,fn($q,$v)=>$q->where('role',$v))
   ->when($filters['status']??null,fn($q,$v)=>$q->where('status',$v))
   ->latest()->paginate(20)->withQueryString();
  return view('admin.users.index',compact('users'));
 }
 public function suspend(Request $request, User $user) {
  $data=$request->validate(['reason'=>'required|string|min:10|max:1000']);
  abort_if($request->user()->is($user),422,'No puedes suspender tu propia cuenta.');
  abort_if($user->status==='suspended',422,'La cuenta ya está suspendida.');
  DB::transaction(function()use($user,$request,$data){
   $user->update(['status'=>'suspended','suspended_at'=>now(),'suspension_reason'=>$data['reason'],'suspended_by'=>$request->user()->id,'remember_token'=>null]);
   $user->driverProfile?->update(['is_online'=>false]);
   UserNotification::create(['user_id'=>$user->id,'type'=>'account_suspended','title'=>'Cuenta suspendida','message'=>'Tu cuenta fue suspendida. Motivo: '.$data['reason']]);
  });
  return back()->with('success','Cuenta suspendida correctamente.');
 }
 public function reactivate(Request $request, User $user) {
  abort_if($user->status==='active',422,'La cuenta ya está activa.');
  $user->update(['status'=>'active','suspended_at'=>null,'suspension_reason'=>null,'suspended_by'=>null]);
  UserNotification::create(['user_id'=>$user->id,'type'=>'account_reactivated','title'=>'Cuenta reactivada','message'=>'Tu cuenta ha sido reactivada y ya puedes ingresar nuevamente.']);
  return back()->with('success','Cuenta reactivada correctamente.');
 }
}
