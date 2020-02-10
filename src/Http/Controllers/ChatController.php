<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Models\{Conversation, SimpleUser};

class ChatController extends Controller
{

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('videochat.index');
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conversation = Chat::addParticipant($request->get('conversation'), Chat::getUser($request));
        $routeParams = ['id'=>$conversation->id];
        if (config('laravel-video-chat.settings.simple-users')) {
            $routeParams['user'] = $request->get('user');
        }
        return redirect()->route('chat.show', $routeParams);
    }

    public function show($id, Request $request)
    {
        $conversation = Chat::getConversationMessageById($id);

        return view('videochat.chat')->with([
            'conversation' => $conversation,
            'user' => Chat::getUser($request)
        ]);
    }

    public function send(Request $request)
    {
        Chat::sendMessage($request->input('conversationId'), $request->input('text'));
    }


    public function sendFilesInConversation(Request $request)
    {
        return Chat::sendFiles($request->input('conversationId') , $request->file('files'));
    }

}
