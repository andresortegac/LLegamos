<?php
namespace App\Http\Controllers;
use App\Models\TripIncident;
use Illuminate\Http\Request;
class IncidentAdminController extends Controller
{
 public function index(){return view('safety.admin-index',['incidents'=>TripIncident::with(['trip','reporter'])->latest()->paginate(40),'user'=>session('user')]);}
 public function show(int $id){return view('safety.admin-show',['incident'=>TripIncident::with(['trip.passenger','trip.driver','reporter'])->findOrFail($id),'user'=>session('user')]);}
 public function update(Request $request,int $id){$data=$request->validate(['status'=>'required|in:open,in_review,resolved,dismissed','resolution_notes'=>'nullable|string|max:2000']);$incident=TripIncident::findOrFail($id);$incident->update(['status'=>$data['status'],'resolution_notes'=>$data['resolution_notes']??null,'assigned_admin_id'=>$request->user()->id,'resolved_at'=>in_array($data['status'],['resolved','dismissed'],true)?now():null]);return back()->with('success','Incidente actualizado.');}
}