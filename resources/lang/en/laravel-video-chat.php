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
        'group_users_table'         => 'conversations_users',
        'files_table'               => 'files',
    ],
    'channel' => [
        'new_conversation_created' => 'new-conversation-created',
        'chat_room'                => 'chat-room',
    ],
    'upload' => [
        'storage' => 'public',
    ],
];
