<?php
namespace App\Jobs;
use App\Services\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
final class SendWebPush implements ShouldQueue { use Dispatchable,InteractsWithQueue,Queueable,SerializesModels; public int $tries=3; public int $timeout=30; public function __construct(public readonly int $userId,public readonly array $message){$this->onQueue('notifications');} public function handle(WebPushService $push): void{$push->sendToUser($this->userId,$this->message);} }