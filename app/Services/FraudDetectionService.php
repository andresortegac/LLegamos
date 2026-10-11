<?php
namespace App\Services;
use App\Models\FraudAlert;
use App\Models\Trip;
class FraudDetectionService {
 public function inspectNewTrip(Trip $trip): void {
  $recent=Trip::where('passenger_id',$trip->passenger_id)->where('created_at','>=',now()->subHour())->count();
  if($recent>=4)$this->alert($trip->passenger_id,$trip,'rapid_trip_requests','high','Múltiples solicitudes de viaje en una hora.',['requests_last_hour'=>$recent]);
  if((float)$trip->distance_km>150)$this->alert($trip->passenger_id,$trip,'unusually_long_route','medium','Ruta inusualmente extensa para revisión.',['distance_km'=>(float)$trip->distance_km]);
 }
 public function inspectCancellation(Trip $trip): void {
  $count=Trip::where('passenger_id',$trip->passenger_id)->where('status','cancelled')->where('cancelled_at','>=',now()->subDay())->count();
  if($count>=3)$this->alert($trip->passenger_id,$trip,'excessive_cancellations','high','Cantidad elevada de cancelaciones en 24 horas.',['cancellations_24h'=>$count]);
 }
 public function inspectCompletion(Trip $trip): void {
  if(!$trip->start_time||!$trip->end_time)return;
  $minutes=$trip->start_time->diffInSeconds($trip->end_time)/60;
  if($minutes<2)$this->alert($trip->driver_id,$trip,'impossibly_short_trip','high','Viaje completado en un tiempo anormalmente corto.',['duration_minutes'=>round($minutes,2)]);
  elseif($minutes>720)$this->alert($trip->driver_id,$trip,'unusually_long_duration','medium','Viaje activo durante más de 12 horas.',['duration_minutes'=>round($minutes,2)]);
 }
 private function alert(?int $userId,Trip $trip,string $rule,string $severity,string $summary,array $details): void {
  FraudAlert::firstOrCreate(['fingerprint'=>hash('sha256',$rule.':'.$trip->id)],['user_id'=>$userId,'trip_id'=>$trip->id,'rule'=>$rule,'severity'=>$severity,'status'=>'open','summary'=>$summary,'details'=>$details,'detected_at'=>now()]);
 }
}
