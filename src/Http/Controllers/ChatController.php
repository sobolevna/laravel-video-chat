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
     * Join existing conversation or start a new one
     * 
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $conversation = Chat::addParticipant($request->get('conversation'), auth()->user()->id);
        return ['conversationId'=>$conversation->id];
    }

    /**
     * Get conversation data
     * 
     * @param int $id Conversation id
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $conversation = Chat::getConversationMessageById($id);

        return [
            'conversation' => $conversation
        ];
    }

    /**
     * Send a message
     * 
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function send(Request $request)
    {
        Chat::sendMessage($request->input('conversationId'), $request->input('text'));
    }

    /**
     * Prepare a file to be sent
     * 
     * @param Request
     * @return \Illuminate\Http\Response
     */
    public function sendFilesInConversation(Request $request)
    {
        return Chat::sendFiles($request->input('conversationId') , $request->file('files'));
    }

    /**
     * Get all video recordings of a conversation
     * 
     * @param int $id Conversation id
     * @return \Illuminate\Http\Response
     */
    public function recordings($id) {
        return Chat::recordings()->recordings($id);
    }

    /**
     * Get recording preview image 
     * 
     * @param int $id Recording id
     * @return \Illuminate\Http\Response
     */
    public function preview($id) {
        return Chat::recordings()->preview($id);
    }

    /**
     * Get recording video
     * 
     * @param int $id Recording id
     * @return \Illuminate\Http\Response
     */
    public function video($id) {
        return Chat::recordings()->video($id);
    }
}
