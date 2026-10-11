<?php
namespace App\Http\Controllers;
use App\Models\DriverProfile;
use App\Models\Trip;
use App\Models\TripRating;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class TripRatingController extends Controller
{
    public function store(Request $request, int $tripId)
    {
        $validated = $request->validate(['rating'=>'required|integer|between:1,5','comment'=>'nullable|string|max:500']);
        $trip = Trip::findOrFail($tripId);
        $user = $request->user();
        abort_unless($trip->status === 'completed', 422, 'El viaje aun no ha terminado.');
        $revieweeId = $trip->passenger_id === $user->id ? $trip->driver_id : ($trip->driver_id === $user->id ? $trip->passenger_id : null);
        abort_unless($revieweeId, 403);
        if (TripRating::where('trip_id', $trip->id)->where('reviewer_id', $user->id)->exists()) {
            return back()->withErrors(['rating' => 'Ya calificaste este viaje.']);
        }
        DB::transaction(function () use ($trip, $user, $revieweeId, $validated) {
            TripRating::create(['trip_id'=>$trip->id,'reviewer_id'=>$user->id,'reviewee_id'=>$revieweeId,'rating'=>$validated['rating'],'comment'=>$validated['comment'] ?? null]);
            if ($revieweeId === $trip->driver_id) {
                $average = TripRating::where('reviewee_id', $revieweeId)->avg('rating');
                DriverProfile::where('user_id', $revieweeId)->update(['rating'=>round($average, 1)]);
            }
            UserNotification::create(['user_id'=>$revieweeId,'type'=>'rating','title'=>'Nueva calificacion','message'=>'Recibiste una calificacion de '.$validated['rating'].' estrellas.','action_url'=>route('trip.show',$trip->id)]);
        });
        return back()->with('success', 'Calificacion registrada.');
    }
}