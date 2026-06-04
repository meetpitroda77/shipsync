<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('shipments', function () {
    return true;
});

Broadcast::channel('user.{id}', function ($user, $id) {

    Log::info('Broadcast Auth User', [
        'auth_user' => $user?->id,
        'channel_id' => $id
    ]);

    return (int) $user->id === (int) $id;
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('staff', function ($user) {
    return in_array($user->role, ['admin', 'staff']);
});
