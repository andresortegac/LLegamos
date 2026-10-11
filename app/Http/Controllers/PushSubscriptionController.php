<?php
namespace App\Http\Controllers;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
class PushSubscriptionController extends Controller {
 public function key(){return response()->json(['public_key'=>config('push.public_key')]);}
 public function store(Request $request){$d=$request->validate(['endpoint'=>'required|url|max:2048','keys.p256dh'=>'required|string|max:512','keys.auth'=>'required|string|max:255','contentEncoding'=>'nullable|in:aesgcm,aes128gcm']);$hash=hash('sha256',$d['endpoint']);PushSubscription::updateOrCreate(['endpoint_hash'=>$hash],['user_id'=>$request->user()->id,'endpoint'=>$d['endpoint'],'public_key'=>$d['keys']['p256dh'],'auth_token'=>$d['keys']['auth'],'content_encoding'=>$d['contentEncoding']??'aes128gcm','user_agent'=>substr((string)$request->userAgent(),0,500),'last_used_at'=>now()]);return response()->json(['subscribed'=>true]);}
 public function destroy(Request $request){$d=$request->validate(['endpoint'=>'required|url|max:2048']);PushSubscription::where('endpoint_hash',hash('sha256',$d['endpoint']))->where('user_id',$request->user()->id)->delete();return response()->json(['subscribed'=>false]);}
}