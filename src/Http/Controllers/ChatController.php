<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Illuminate\Routing\Controller;
use Storage;

class ChatController extends Controller
{
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Chat::getAllConversations();
    }
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conversation = Chat::addParticipant($request->get('conversation'), auth()->user()->id);
        return ['conversationId'=>$conversation->id];
    }

    public function show($id, Request $request)
    {
        $conversation = Chat::getConversationMessageById($id);

        return [
            'conversation' => $conversation
        ];
    }

    public function send(Request $request)
    {
        Chat::sendMessage($request->input('conversationId'), $request->input('text'));
    }


    public function sendFilesInConversation(Request $request)
    {
        return Chat::sendFiles($request->input('conversationId') , $request->file('files'));
    }

    public function recordings($id) {
        return Chat::recordings()->recordings($id);
    }

    public function preview($id) {
        return Chat::recordings()->preview($id);
    }

    public function video($id) {
        return Chat::recordings()->video($id);
    }
}
