<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('shipments', function () {
    return true;
});



Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
