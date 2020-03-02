<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;
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
        $videos = [];
        $i = 0;
        while (true) {
            $video = [];
            $videoId = $i > 0 ? "$id-$i" : $id;
            if (!Storage::exists("video/$videoId/$videoId.mp4")) {
                break;
            }
            if (!Storage::exists("video/$videoId/$videoId.jpg")) {
                continue;
            }
            $video['id'] = $videoId;
            $video['img_preview'] = route('api.chat.preview', $videoId);
            $video['url'] = route('api.chat.video', $videoId);
            $videos[] = video;
            $i++;
        }
        return $videos;
    }

    public function preview($id) {
        $fileContents = Storage::disk('local')->get("video/{$id}/$id.jpg");
        $response = \Illuminate\Support\Facades\Response::make($fileContents, 200);
        $response->header('Content-Type', "image/jpeg");
        return $response;
    }

    public function video($id) {
        $fileContents = Storage::disk('local')->get("video/{$id}/$id.mp4");
        $response = \Illuminate\Support\Facades\Response::make($fileContents, 200);
        $response->header('Content-Type', "video/mp4");
        return $response;
    }
}
