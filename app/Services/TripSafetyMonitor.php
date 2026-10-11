<?php
namespace App\Services;
use App\Models\Trip;
use App\Models\TripIncident;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Cache;
final class TripSafetyMonitor
{
 public function inspect(Trip $trip,float $lat,float $lng,?float $speed=null): void
 {
  if(!in_array($trip->status,['accepted','in_progress'],true)||!$trip->driver_id)return;
  $key='trip-safety:'.$trip->id;$state=Cache::get($key,[]);$now=now();
  if(($speed??1)<0.8){$state['stopped_at']??=$now->timestamp;if($now->timestamp-$state['stopped_at']>=180)$this->signal($trip,'Parada prolongada detectada','El vehiculo permanece detenido por mas de 3 minutos.', 'high',$lat,$lng);}else unset($state['stopped_at']);
  if($trip->status==='in_progress'&&$trip->origin_lat&&$trip->destination_lat){$direct=$this->distance((float)$trip->origin_lat,(float)$trip->origin_lng,(float)$trip->destination_lat,(float)$trip->destination_lng);$via=$this->distance((float)$trip->origin_lat,(float)$trip->origin_lng,$lat,$lng)+$this->distance($lat,$lng,(float)$trip->destination_lat,(float)$trip->destination_lng);if($via-$direct>3)$this->signal($trip,'Posible desvio de ruta','La ubicacion se alejo significativamente del trayecto esperado.','high',$lat,$lng);}
  $state['lat']=$lat;$state['lng']=$lng;$state['seen_at']=$now->timestamp;Cache::put($key,$state,now()->addHours(6));
 }
 public function inspectStaleTrips(): int
 {
  $count=0;Trip::with('driver.driverProfile')->whereIn('status',['accepted','in_progress'])->whereNotNull('driver_id')->get()->each(function($trip)use(&$count){$p=$trip->driver?->driverProfile;if(!$p?->location_updated_at||$p->location_updated_at->lt(now()->subSeconds(75))){$this->signal($trip,'Senal GPS perdida','No recibimos ubicacion reciente del conductor.','high',$p?->current_lat,$p?->current_lng);$count++;}});return $count;
 }
 private function signal(Trip $trip,string $title,string $description,string $severity,?float $lat,?float $lng): void
 {
  $tag='[AUTOMATICA] '.$title;if(TripIncident::where('trip_id',$trip->id)->where('status','open')->where('description','like',$tag.'%')->exists())return;
  $incident=TripIncident::create(['trip_id'=>$trip->id,'reported_by'=>$trip->driver_id,'type'=>'safety','severity'=>$severity,'status'=>'open','description'=>$tag.'. '.$description,'latitude'=>$lat,'longitude'=>$lng]);
  collect([$trip->passenger_id,$trip->driver_id])->filter()->unique()->each(fn($id)=>UserNotification::create(['user_id'=>$id,'type'=>'safety_alert','title'=>$title,'message'=>$description,'action_url'=>route('trip.show',$trip->id)]));
  User::where('role','admin')->pluck('id')->each(fn($id)=>UserNotification::create(['user_id'=>$id,'type'=>'safety_alert','title'=>$title,'message'=>'Viaje #'.$trip->id.': '.$description,'action_url'=>route('admin.incidents.show',$incident->id)]));
 }
 private function distance(float $a,float $b,float $c,float $d):float{$r=6371;$x=deg2rad($c-$a);$y=deg2rad($d-$b);$q=sin($x/2)**2+cos(deg2rad($a))*cos(deg2rad($c))*sin($y/2)**2;return $r*2*atan2(sqrt($q),sqrt(1-$q));}
}