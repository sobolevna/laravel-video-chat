<?php

namespace Sobolevna\LaravelVideoChat\Services;

use Illuminate\Contracts\Config\Repository;
use \Illuminate\Support\Facades\Response;
use Sobolevna\LaravelVideoChat\Repositories\ConversationRepository;
use Sobolevna\LaravelVideoChat\Models\{Conversation};
use Storage;

/**
 * @author sobolevna 
 * 
 */
class Recordings
{
    /**
     * Get all video recordings of a conversation
     * 
     * @param int $id Conversation id
     * @return array
     */
    public function recordings($sessionId) {
        $videos = [];
        $i = 0;
        while (true) {
            $video = [];
            $videoId = $i > 0 ? "$sessionId-$i" : $sessionId;
            if (!Storage::exists("video/$videoId/$videoId.mp4")) {
                break;
            }
            $i++;
            if (!Storage::exists("video/$videoId/$videoId.jpg")) {
                continue;
            }
            $video = json_decode(Storage::get("video/$videoId/.recording.$videoId"), true);
            $video['img_preview'] = route('chat.preview', $videoId);
            $video['url'] = route('chat.video', $videoId);
            $videos[] = $video;
        }
        return $videos;
    }

    /**
     * Get recording preview image 
     * 
     * @param int $id Recording id
     * @return \Illuminate\Http\Response
     */
    public function preview($videoId) {
        $fileContents = Storage::disk('local')->get("video/{$videoId}/$videoId.jpg");
        $response = Response::make($fileContents, 200);
        $response->header('Content-Type', "image/jpeg");
        return $response;
    }

    /**
     * Get recording preview image 
     * 
     * @param int $id Recording id
     * @return \Illuminate\Http\Response
     */
    public function video($videoId) {
        $fileContents = Storage::disk('local')->get("video/{$videoId}/$videoId.mp4");
        $response = Response::make($fileContents, 200);
        $response->header('Content-Type', "video/mp4");
        return $response;
    }
}
