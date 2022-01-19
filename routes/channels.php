<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('chats.{chatUuid}.messages', function ($user, $chatUuid) {
    $userChat = \App\Models\Chat\UserChat::where('user_chat_chat_uuid', $chatUuid)->where('user_chat_user_id', $user->id)->first();
    return !is_null($userChat) && (int) $user->id === (int) $userChat->user_id;
});

Broadcast::channel('App.Messages.User.{id}', function ($user) {
    return auth()->check();
});
