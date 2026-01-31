<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;

Broadcast::channel('tickets', function ($user) {
    return true;
});

Broadcast::channel('user.{id}', function (User $user, int $id) {
    return $user->id === $id;
});

Broadcast::channel('admin', function (User $user) {
    return $user->isAdmin();
});

Broadcast::channel('managers', function (User $user) {
    return $user->canManageTickets();
});
