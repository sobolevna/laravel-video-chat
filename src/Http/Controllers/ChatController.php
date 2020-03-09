<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Illuminate\Routing\Controller;
use Storage;

class ChatController extends Controller
{
    protected $recordings;

    public function __construct() {
        parent::__construct();
        $this->recordings = new Recordings();
    }

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
        return $this->recordings->recordings($id);
    }

    public function preview($id) {
        return $this->recordings->preview($id);
    }

    public function video($id) {
        return $this->recordings->video($id);
    }
}
