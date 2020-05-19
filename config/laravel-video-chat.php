<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 10/16/17
 * Time: 1:51 PM.
 */

return [
    'user' => [
        'model' => App\User::class,
        'table' => 'users', // Existing user table name
    ],
    'table' => [
        'conversations_table'       => 'conversations',
        'messages_table'            => 'messages',
        'conversations_users_table' => 'conversations_users',
        'files_table'               => 'files',
        'participants_table'        => 'participants',
        'connections_table'         => 'connections',
        'recordings_table'          => 'recordings',
        'openvidu_events_table'       => 'openvidu_events'
    ],
    'channel' => [
        'new_conversation_created' => 'new-conversation-created',
        'chat_room'                => 'chat-room',
    ],
    'upload' => [
        'storage' => 'public',
    ],
    'settings' => [
        'use-package-routes' =>true,
        'controller' => \Sobolevna\LaravelVideoChat\Http\Controllers\ChatController::class,
    ],
    'recording' => env('OPENVIDU_RECORDING', true)
];
