<?php
namespace App\Http\Middleware;
use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
class AuditMutatingRequests
{
 public function handle(Request $request,Closure $next): Response
 {
  $response=$next($request);
  if($request->user() && in_array($request->method(),['POST','PUT','PATCH','DELETE'],true) && Schema::hasTable('audit_logs')){
   AuditLog::create(['user_id'=>$request->user()->id,'event'=>$request->route()?->getName()??'http.mutation','route_name'=>$request->route()?->getName(),'method'=>$request->method(),'path'=>$request->path(),'response_status'=>$response->getStatusCode(),'ip_address'=>$request->ip(),'user_agent'=>substr((string)$request->userAgent(),0,500),'context'=>['controller'=>$request->route()?->getActionName()]]);
  }
  return $response;
 }
}