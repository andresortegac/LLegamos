<?php
namespace App\Http\Controllers;
use App\Models\DriverEarning;
use App\Models\PayoutRequest;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class PayoutAdminController extends Controller
{
 public function index(){return view('payouts.admin',['requests'=>PayoutRequest::with(['driver','account'])->latest()->paginate(40),'user'=>session('user')]);}
 public function update(Request $request,int $id){$data=$request->validate(['status'=>'required|in:approved,paid,rejected','payment_reference'=>'required_if:status,paid|nullable|string|max:150','review_notes'=>'nullable|string|max:1000']);DB::transaction(function()use($request,$id,$data){$p=PayoutRequest::lockForUpdate()->findOrFail($id);$allowed=['pending'=>['approved','rejected'],'approved'=>['paid','rejected']];abort_unless(in_array($data['status'],$allowed[$p->status]??[],true),422,'Transicion de retiro invalida.');$p->update(['status'=>$data['status'],'reviewed_by'=>$request->user()->id,'payment_reference'=>$data['payment_reference']??null,'review_notes'=>$data['review_notes']??null,'reviewed_at'=>now(),'paid_at'=>$data['status']==='paid'?now():null]);if($data['status']==='rejected'){DriverEarning::where('payout_request_id',$p->id)->update(['payout_request_id'=>null]);}if($data['status']==='paid'){DriverEarning::where('payout_request_id',$p->id)->update(['status'=>'paid','paid_at'=>now()]);}$message=$data['status']==='paid'?'Tu retiro fue pagado.':($data['status']==='rejected'?'Tu retiro fue rechazado y el saldo fue liberado.':'Tu retiro fue aprobado.');UserNotification::create(['user_id'=>$p->driver_id,'type'=>'payout','title'=>'Actualizacion de retiro','message'=>$message,'action_url'=>route('payouts.driver')]);});return back()->with('success','Retiro actualizado.');}
}