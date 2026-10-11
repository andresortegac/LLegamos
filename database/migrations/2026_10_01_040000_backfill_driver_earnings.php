<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    public function up(): void
    {
        $rate = (float) config('monetization.commission_rate', 12);
        DB::table('trips')->where('status','completed')->whereNotNull('driver_id')->orderBy('id')->eachById(function ($trip) use ($rate): void {
            $gross = (float) ($trip->final_cost ?? $trip->estimated_cost ?? 0);
            if ($gross <= 0) return;
            $fee = round($gross * ($rate / 100), 2);
            $paid = DB::table('payments')->where('trip_id',$trip->id)->where('status','completed')->exists();
            DB::table('driver_earnings')->insertOrIgnore([
                'trip_id'=>$trip->id,'driver_id'=>$trip->driver_id,'gross_amount'=>$gross,'commission_rate'=>$rate,
                'platform_fee'=>$fee,'net_amount'=>round($gross-$fee,2),'status'=>$paid?'available':'pending',
                'available_at'=>$paid?now():null,'created_at'=>now(),'updated_at'=>now(),
            ]);
        });
    }
    public function down(): void {}
};