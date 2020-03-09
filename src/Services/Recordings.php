<?php

namespace Sobolevna\LaravelVideoChat\Services;

use Illuminate\Contracts\Config\Repository;
use \Illuminate\Support\Facades\Response;
use Sobolevna\LaravelVideoChat\Repositories\ConversationRepository;
use Sobolevna\LaravelVideoChat\Models\{Conversation};
use Storage;

class Recordings
{
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
            $video['id'] = $videoId;
            $video['img_preview'] = route('chat.preview', $videoId);
            $video['url'] = route('chat.video', $videoId);
            $videos[] = $video;
        }
        return $videos;
    }

    public function preview($videoId) {
        $fileContents = Storage::disk('local')->get("video/{$videoId}/$videoId.jpg");
        $response = Response::make($fileContents, 200);
        $response->header('Content-Type', "image/jpeg");
        return $response;
    }

    public function video($videoId) {
        $fileContents = Storage::disk('local')->get("video/{$videoId}/$videoId.mp4");
        $response = Response::make($fileContents, 200);
        $response->header('Content-Type', "video/mp4");
        return $response;
    }
}
