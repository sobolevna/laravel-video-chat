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
