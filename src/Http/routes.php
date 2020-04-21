<?php

use Illuminate\Support\Facades\Route;

Route::group([
    'namespace' => 'Sobolevna\LaravelVideoChat\Http\Controllers',
    'prefix' => 'api/chat',
    'as' => 'api.chat.',
    'middleware'=>['api', 'auth:api'],
], function(){
    Route::get('/', 'ChatController@index')->name('index');
    Route::post('/', 'ChatController@store')->name('store');
    Route::get('/{id}', 'ChatController@show')->name('show');    
    Route::apiResource([
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

Route::group([
    'namespace' => 'Sobolevna\LaravelVideoChat\Http\Controllers',
    'prefix' => 'chat',
    'as'=> 'chat.'
], function(){
    Route::get('/{id}/preview', 'ChatController@preview')->name('preview');
    Route::get('/{id}/video', 'ChatController@video')->name('video');
});

