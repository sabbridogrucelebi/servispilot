<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('pilotcell.trip.{tripId}', function ($user, $tripId) {
    $trip = \App\Models\PilotCell\PcTrip::find($tripId);
    return $trip && $trip->company_id === $user->company_id;
});
