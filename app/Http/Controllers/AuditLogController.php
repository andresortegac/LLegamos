<?php
namespace App\Http\Controllers;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
class AuditLogController extends Controller {
 public function index(Request $request) {
  $filters=$request->validate(['user_id'=>'nullable|integer|exists:users,id','event'=>'nullable|string|max:100','method'=>'nullable|in:POST,PUT,PATCH,DELETE','from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from']);
  $logs=AuditLog::query()->with('user:id,name,email')
   ->when($filters['user_id']??null,fn($q,$v)=>$q->where('user_id',$v))
   ->when($filters['event']??null,fn($q,$v)=>$q->where('event','like',"%{$v}%"))
   ->when($filters['method']??null,fn($q,$v)=>$q->where('method',$v))
   ->when($filters['from']??null,fn($q,$v)=>$q->whereDate('created_at','>=',$v))
   ->when($filters['to']??null,fn($q,$v)=>$q->whereDate('created_at','<=',$v))
   ->orderByDesc('created_at')->paginate(50)->withQueryString();
  $users=User::orderBy('name')->get(['id','name','email']);
  return view('admin.audit.index',compact('logs','users'));
 }
}
