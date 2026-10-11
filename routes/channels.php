<?php

use App\Models\Trip;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', fn ($user, $id) => (int) $user->id === (int) $id);
Broadcast::channel('users.{id}', fn ($user, $id) => (int) $user->id === (int) $id);
Broadcast::channel('trips.{tripId}', function ($user, int $tripId) {
    return Trip::whereKey($tripId)->where(fn ($query) => $query
        ->where('passenger_id', $user->id)
        ->orWhere('driver_id', $user->id))
        ->exists();
});