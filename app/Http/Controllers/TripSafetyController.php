<?php
namespace App\Http\Controllers;
use App\Models\Trip;
use App\Models\TripIncident;
use App\Models\TripShareToken;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
class TripSafetyController extends Controller
{
 public function share(Request $request,int $tripId){$trip=$this->participantTrip($request,$tripId);abort_unless(in_array($trip->status,['accepted','in_progress'],true),422);$raw=Str::random(64);TripShareToken::create(['trip_id'=>$trip->id,'created_by'=>$request->user()->id,'token_hash'=>hash('sha256',$raw),'expires_at'=>now()->addHours(12)]);return back()->with('share_url',route('trip.shared',$raw));}
 public function shared(Request $request,string $token){$share=TripShareToken::with('trip.driver.driverProfile')->where('token_hash',hash('sha256',$token))->whereNull('revoked_at')->where('expires_at','>',now())->firstOrFail(); $trip=$share->trip; if($request->expectsJson()){$profile=$trip->driver?->driverProfile;$fresh=$profile?->location_updated_at?->greaterThan(now()->subMinutes(2))??false;return response()->json(['status'=>$trip->status,'driver'=>$trip->driver?->name,'latitude'=>$fresh?(float)$profile->current_lat:null,'longitude'=>$fresh?(float)$profile->current_lng:null]);} return view('trip.shared',['trip'=>$trip,'share'=>$share]);}
 public function revoke(Request $request,int $tripId){$this->participantTrip($request,$tripId);TripShareToken::where('trip_id',$tripId)->whereNull('revoked_at')->update(['revoked_at'=>now()]);return back()->with('success','Enlaces compartidos revocados.');}
 public function sos(Request $request,int $tripId){$validated=$request->validate(['latitude'=>'nullable|numeric|between:-90,90','longitude'=>'nullable|numeric|between:-180,180']);$trip=$this->participantTrip($request,$tripId);abort_unless(in_array($trip->status,['accepted','in_progress'],true),422);$incident=TripIncident::firstOrCreate(['trip_id'=>$trip->id,'reported_by'=>$request->user()->id,'type'=>'sos','status'=>'open'],['severity'=>'critical','description'=>'Alerta SOS activada durante el viaje.','latitude'=>$validated['latitude']??null,'longitude'=>$validated['longitude']??null]);User::where('role','admin')->pluck('id')->each(fn($id)=>UserNotification::firstOrCreate(['user_id'=>$id,'type'=>'sos','action_url'=>route('admin.incidents.show',$incident->id)],['title'=>'ALERTA SOS','message'=>'Alerta critica en el viaje #'.$trip->id]));return back()->with('error','Alerta SOS registrada. Contacta tambien a la linea local de emergencias si existe peligro inmediato.');}
 public function report(Request $request,int $tripId){$validated=$request->validate(['type'=>'required|in:safety,conduct,vehicle,lost_item,other','description'=>'required|string|min:10|max:2000']);$trip=$this->participantTrip($request,$tripId);TripIncident::create(['trip_id'=>$trip->id,'reported_by'=>$request->user()->id,'type'=>$validated['type'],'severity'=>'medium','description'=>$validated['description']]);return back()->with('success','Reporte enviado al equipo de soporte.');}
 private function participantTrip(Request $request,int $id): Trip{$trip=Trip::findOrFail($id);abort_unless(in_array($request->user()->id,[$trip->passenger_id,$trip->driver_id],true),403);return $trip;}
}