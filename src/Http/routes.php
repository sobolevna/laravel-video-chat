<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'ChatController@index')->name('index');
Route::post('/', 'ChatController@store')->name('store');
Route::get('/{id}', 'ChatController@show')->name('show');    
Route::post('/message/send', 'ChatController@send')->name('send');
Route::post('/message/send/file', 'ChatController@sendFilesInConversation')->name('send.file');
Route::post('/trigger/{id}' , function (\Illuminate\Http\Request $request , $id) {
    Chat::startVideoCall($id , $request->all());
})->name('call');
Route::post('/chat/leave/{id}' , function ($id) {
    Chat::leaveFromGroupConversation($id);
});

Route::get('/{id}/recordings', 'ChatController@recordings')->name('recordings');
Route::get('/{id}/preview', 'ChatController@preview')->name('preview');
Route::get('/{id}/video', 'ChatController@video')->name('video');
