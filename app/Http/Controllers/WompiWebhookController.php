<?php
namespace App\Http\Controllers;use App\Services\WompiPaymentService;use Illuminate\Http\Request;
class WompiWebhookController extends Controller{public function __invoke(Request $request,WompiPaymentService $wompi){try{$wompi->handle($request->all(),$request->header('X-Event-Checksum'));return response()->json(['received'=>true]);}catch(\InvalidArgumentException $e){return response()->json(['message'=>'Firma invalida.'],401);}}}
