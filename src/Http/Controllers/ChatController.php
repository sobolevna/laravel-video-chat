<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;

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
        $conversation = Chat::addParticipant($request->get('conversation'), auth()->user()->id);
        $routeParams = ['id'=>$conversation->id];
        return redirect()->route('chat.show', $routeParams);
    }

    public function show($id, Request $request)
    {
        $conversation = Chat::getConversationMessageById($id);

        return view('videochat.chat')->with([
            'conversation' => $conversation,
            'user' => auth()->user()
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
