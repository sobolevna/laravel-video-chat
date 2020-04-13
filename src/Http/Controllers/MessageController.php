<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Illuminate\Routing\Controller;
use Storage;

class MessageController extends Controller
{
    
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
}
