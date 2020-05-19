<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Sobolevna\LaravelVideoChat\Http\Controllers',
    'prefix' => 'api/chat',
    'as' => 'api.chat.',
    'middleware'=>['api', 'auth:api'],
], function(){

    Route::post('/enter', 'ConversationController@enter')->name('conversations.enter');

    Route::post('/token', '\SquareetLabs\LaravelOpenVidu\Http\Controllers\OpenViduController@token')->name('token');     

    Route::resources([
        'conversations'=> 'ConversationController',
        'conversations.participants' => 'ParticipantController',
        'conversations.messages' => 'MessageController',
        'conversations.files' => 'FileController',
    ]);
    
    Route::group([
        'prefix'=> 'message',
        'as' => 'message.'
    ], function(){
        Route::post('/send', 'MessageController@send')->name('send');
        Route::post('/send/file', 'MessageController@sendFilesInConversation')->name('send.file');
    });

    Route::post('/{id}/call/start' , 'CallController@start')->name('call.start');
    Route::post('/{id}/call/finish' , 'CallController@finish')->name('call.finish');

    Route::post('/leave/{id}' , function ($id) {
        Chat::leaveFromGroupConversation($id);
    });
    Route::get('/{id}/recordings', 'ChatController@recordings')->name('recordings');
});

Route::post('/api/chat/webhook', '\SquareetLabs\LaravelOpenVidu\Http\Controllers\OpenViduController@webhook')->middleware(['api'])->name('api.chat.webhook');

Route::group([
    'namespace' => 'Sobolevna\LaravelVideoChat\Http\Controllers',
    'prefix' => 'chat',
    'as'=> 'chat.',
    'middleware'=>['auth:api']
], function(){    
    Route::get('/{id}/preview', 'ChatController@preview')->name('preview');
    Route::get('/{id}/video', 'ChatController@video')->name('video');
});

