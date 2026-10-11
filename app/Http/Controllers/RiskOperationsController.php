<?php
namespace App\Http\Controllers;
use App\Models\AuditLog;
use App\Models\FraudAlert;
use App\Models\Payment;
use App\Models\Trip;
use App\Models\TripIncident;
use App\Models\User;
use Illuminate\Http\Request;
class RiskOperationsController extends Controller {
 public function index(Request $request) {
  $filters=$request->validate(['status'=>'nullable|in:open,reviewed,dismissed','severity'=>'nullable|in:low,medium,high,critical','rule'=>'nullable|string|max:100']);
  $alerts=FraudAlert::with(['user:id,name,email','trip:id,status,origin,destination'])
   ->when($filters['status']??null,fn($q,$v)=>$q->where('status',$v))
   ->when($filters['severity']??null,fn($q,$v)=>$q->where('severity',$v))
   ->when($filters['rule']??null,fn($q,$v)=>$q->where('rule',$v))
   ->latest('detected_at')->paginate(30)->withQueryString();
  $metrics=[
   'open_alerts'=>FraudAlert::where('status','open')->count(),
   'critical_incidents'=>TripIncident::whereIn('status',['open','investigating'])->whereIn('severity',['high','critical'])->count(),
   'active_trips'=>Trip::whereIn('status',['accepted','in_progress'])->count(),
   'pending_payments'=>Payment::where('status','pending')->count(),
   'suspended_users'=>User::where('status','suspended')->count(),
   'audit_events_24h'=>AuditLog::where('created_at','>=',now()->subDay())->count(),
  ];
  return view('admin.risk.index',compact('alerts','metrics'));
 }
 public function update(Request $request,FraudAlert $alert) {
  $data=$request->validate(['status'=>'required|in:reviewed,dismissed','review_notes'=>'required|string|min:5|max:1000']);
  $alert->update($data+['reviewed_by'=>$request->user()->id,'reviewed_at'=>now()]);
  return back()->with('success','Alerta revisada correctamente.');
 }
}
