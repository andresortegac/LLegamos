<?php
namespace App\Services;
use App\Models\DriverProfile;
use App\Models\Trip;
use App\Models\TripOffer;
use App\Models\UserNotification;
use App\Jobs\SendWebPush;
use Illuminate\Support\Facades\DB;
final class DispatchService
{
 public function __construct(private readonly RealtimePublisher $realtime) {}

 public function dispatch(Trip $trip): ?TripOffer
 {
  return DB::transaction(function()use($trip){
   $locked=Trip::query()->lockForUpdate()->find($trip->id);
   if(!$locked || $locked->status!=='pending' || $locked->driver_id)return null;
   $active=TripOffer::query()->where('trip_id',$locked->id)->where('status','pending')->lockForUpdate()->first();
   if($active && $active->expires_at->isFuture())return $active;
   if($active){$active->update(['status'=>'expired','responded_at'=>now()]);}
   $excluded=TripOffer::where('trip_id',$locked->id)->pluck('driver_id');
   $required=$locked->vehicle_type==='carro'?'auto':'moto';
   $busy=Trip::whereIn('status',['accepted','in_progress'])->whereNotNull('driver_id')->pluck('driver_id');
   $profiles=DriverProfile::query()->with('user')->where('verification_status','approved')->where('status','active')->where('is_online',true)->where('vehicle_type',$required)->whereNotNull('current_lat')->whereNotNull('current_lng')->where('location_updated_at','>=',now()->subSeconds(config('dispatch.location_fresh_seconds')))->whereNotIn('user_id',$excluded)->whereNotIn('user_id',$busy)->get();
   $candidate=$profiles->map(function($profile)use($locked){$profile->dispatch_distance=$this->distance((float)$profile->current_lat,(float)$profile->current_lng,(float)$locked->origin_lat,(float)$locked->origin_lng);return $profile;})->filter(fn($profile)=>$profile->dispatch_distance<=config('dispatch.max_radius_km'))->sortBy(fn($profile)=>sprintf('%012.4f-%05.2f',$profile->dispatch_distance,5-(float)$profile->rating))->first();
   if(!$candidate)return null;
   $offer=TripOffer::create(['trip_id'=>$locked->id,'driver_id'=>$candidate->user_id,'distance_km'=>round($candidate->dispatch_distance,2),'status'=>'pending','expires_at'=>now()->addSeconds(config('dispatch.offer_seconds'))]);
   UserNotification::create(['user_id'=>$candidate->user_id,'type'=>'trip_offer','title'=>'Nueva solicitud cercana','message'=>'Tienes '.config('dispatch.offer_seconds').' segundos para responder al viaje #'.$locked->id.'.','action_url'=>route('trip.list-pending')]);
   DB::afterCommit(function()use($candidate,$locked,$offer){$this->realtime->user((int)$candidate->user_id,'trip.offer',(int)$locked->id,['expires_at'=>$offer->expires_at->toIso8601String()]);SendWebPush::dispatch((int)$candidate->user_id,['title'=>'Nueva solicitud cercana','body'=>'Tienes '.config('dispatch.offer_seconds').' segundos para responder.','url'=>route('trip.list-pending'),'tag'=>'trip-offer-'.$locked->id]);});
   return $offer;
  },3);
 }
 public function accept(TripOffer $offer,int $driverId): Trip
 {
  return DB::transaction(function()use($offer,$driverId){
   $lockedOffer=TripOffer::query()->lockForUpdate()->findOrFail($offer->id);
   abort_unless($lockedOffer->driver_id===$driverId && $lockedOffer->isActionable(),409,'La oferta ya no esta disponible.');
   $trip=Trip::query()->lockForUpdate()->findOrFail($lockedOffer->trip_id);
   abort_unless($trip->status==='pending' && !$trip->driver_id,409,'El viaje ya fue asignado.');
   $trip->update(['driver_id'=>$driverId,'status'=>'accepted','driver_distance_km'=>$lockedOffer->distance_km]);
   $lockedOffer->update(['status'=>'accepted','responded_at'=>now()]);
   TripOffer::where('trip_id',$trip->id)->where('id','!=',$lockedOffer->id)->where('status','pending')->update(['status'=>'expired','responded_at'=>now()]);
   UserNotification::create(['user_id'=>$trip->passenger_id,'type'=>'trip_accepted','title'=>'Conductor asignado','message'=>'Un conductor acepto tu viaje #'.$trip->id.'.','action_url'=>route('trip.show',$trip->id)]);
   DB::afterCommit(fn()=>$this->realtime->trip($trip,'trip.updated',['status'=>'accepted']));
   return $trip;
  },3);
 }
 public function reject(TripOffer $offer,int $driverId): void
 {
  DB::transaction(function()use($offer,$driverId){$locked=TripOffer::query()->lockForUpdate()->findOrFail($offer->id);abort_unless($locked->driver_id===$driverId && $locked->status==='pending',409);$locked->update(['status'=>'rejected','responded_at'=>now()]);DB::afterCommit(fn()=>$this->realtime->user($driverId,'trip.offer.closed',(int)$locked->trip_id));});
  $this->dispatch($offer->trip);
 }
 public function expireAndRedispatch(): int
 {
  $offers=TripOffer::with('trip')->where('status','pending')->where('expires_at','<=',now())->limit(config('dispatch.batch_size'))->get();
  foreach($offers as $offer){$changed=TripOffer::whereKey($offer->id)->where('status','pending')->update(['status'=>'expired','responded_at'=>now()]);if($changed)$this->dispatch($offer->trip);}
  return $offers->count();
 }
 public function dispatchWaiting(int $limit=20): int
 {
  $trips=Trip::where('status','pending')->whereNull('driver_id')->whereDoesntHave('offers',fn($q)=>$q->where('status','pending')->where('expires_at','>',now()))->oldest()->limit($limit)->get();
  foreach($trips as $trip)$this->dispatch($trip);
  return $trips->count();
 }
 private function distance(float $lat1,float $lng1,float $lat2,float $lng2): float{$earth=6371;$dLat=deg2rad($lat2-$lat1);$dLng=deg2rad($lng2-$lng1);$a=sin($dLat/2)**2+cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLng/2)**2;return $earth*2*atan2(sqrt($a),sqrt(1-$a));}
}
