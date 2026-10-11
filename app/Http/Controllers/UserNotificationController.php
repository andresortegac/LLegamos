<?php
namespace App\Http\Controllers;
use App\Models\UserNotification;
use Illuminate\Http\Request;
class UserNotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = UserNotification::where('user_id',$request->user()->id)->latest()->paginate(30);
        UserNotification::where('user_id',$request->user()->id)->whereNull('read_at')->update(['read_at'=>now()]);
        return view('notifications.index',['notifications'=>$notifications,'user'=>session('user')]);
    }
}