<?php
namespace App\Http\Controllers;
use App\Models\DriverEarning;
use App\Models\PayoutAccount;
use App\Models\PayoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DriverPayoutController extends Controller
{
 public function index(Request $request){$id=$request->user()->id;return view('payouts.driver',['user'=>session('user'),'account'=>PayoutAccount::where('driver_id',$id)->first(),'requests'=>PayoutRequest::where('driver_id',$id)->latest()->get(),'available'=>DriverEarning::where('driver_id',$id)->where('status','available')->whereNull('payout_request_id')->sum('net_amount')]);}
 public function account(Request $request){$data=$request->validate(['holder_name'=>'required|string|max:150','holder_document'=>'required|string|max:40','bank_name'=>'required|string|max:120','account_type'=>'required|in:ahorros,corriente,wallet','account_number'=>'required|string|min:6|max:50']);PayoutAccount::updateOrCreate(['driver_id'=>$request->user()->id],$data+['account_last_four'=>substr($data['account_number'],-4),'is_verified'=>false]);return back()->with('success','Cuenta guardada de forma cifrada.');}
 public function request(Request $request){$driverId=$request->user()->id;$payout=DB::transaction(function()use($driverId){$account=PayoutAccount::where('driver_id',$driverId)->firstOrFail();$earnings=DriverEarning::where('driver_id',$driverId)->where('status','available')->whereNull('payout_request_id')->lockForUpdate()->get();$amount=(float)$earnings->sum('net_amount');abort_if($amount<(float)config('monetization.minimum_payout',20000),422,'No alcanzas el saldo minimo para retirar.');$p=PayoutRequest::create(['driver_id'=>$driverId,'payout_account_id'=>$account->id,'amount'=>$amount,'status'=>'pending']);DriverEarning::whereIn('id',$earnings->pluck('id'))->update(['payout_request_id'=>$p->id]);return $p;});return back()->with('success','Retiro #'.$payout->id.' solicitado por $'.number_format($payout->amount,0,',','.'));}
}