<?php
namespace App\Services;
use App\Models\PushSubscription as StoredSubscription;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
final class WebPushService {
 public function sendToUser(int $userId,array $message): void { if(!config('push.public_key')||!config('push.private_key'))return;$push=new WebPush(['VAPID'=>['subject'=>config('push.subject'),'publicKey'=>config('push.public_key'),'privateKey'=>config('push.private_key')]],['TTL'=>120,'urgency'=>'high']);$stored=StoredSubscription::where('user_id',$userId)->get();foreach($stored as $item){$push->queueNotification(Subscription::create(['endpoint'=>$item->endpoint,'publicKey'=>$item->public_key,'authToken'=>$item->auth_token,'contentEncoding'=>$item->content_encoding]),json_encode($message,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));}foreach($push->flush() as $report){$item=$stored->first(fn($row)=>hash_equals($row->endpoint,$report->getEndpoint()));if(!$item)continue;if($report->isSubscriptionExpired())$item->delete();elseif($report->isSuccess())$item->update(['last_used_at'=>now()]);}}
}