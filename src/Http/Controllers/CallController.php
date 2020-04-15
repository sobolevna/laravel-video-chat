<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Sobolevna\LaravelVideoChat\Events\{
    VideoChatStart, VideoChatFinish
};
use Illuminate\Routing\Controller;
use SquareetLabs\LaravelOpenVidu\OpenVidu;
use SquareetLabs\LaravelOpenVidu\Exceptions\OpenViduRecordingNotFoundException;
use SquareetLabs\LaravelOpenVidu\Exceptions\OpenViduSessionCantRecordingException;
use SquareetLabs\LaravelOpenVidu\Builders\RecordingPropertiesBuilder;
use Storage;

/**
 * @todo Логику перенести в сервисные классы
 */
class CallController extends Controller
{
    protected $openviduController = '\SquareetLabs\LaravelOpenVidu\Http\Controllers\OpenViduController';
    
    /**
     * @todo События сделать как положено
     * @param int $id 
     * @param Request $request
     * @param Openvidu $manager
     * @return \Illuminate\Http\Response
     */
    public function start($id, Request $request, OpenVidu $manager) {
        $session = $manager->getSession($id);
        \broadcast(new VideoChatStart($request->all(), ''));
        if (!$session->isBeingRecorded()) {
            try{ 
                $recording = $manager->startRecording(RecordingPropertiesBuilder::build($request->all()));         
                $message = "Recording successfully started";
            }
            catch(OpenViduSessionCantRecordingException $e) {
                $recording = $this->findLastRecording($id);
                $session->setIsBeingRecorded(true);
                $session->setLastRecordingId($recording['id']);  
                $message = "Session has been recorded, but there wasn't any data of it";              
            }
            return response()->json(['recording' => $recording, "message" => $message], 200);       
        }
        return response()->json(["message" => "Call started with session already being recorded"], 200);
    }

    /**
     * @todo Вот это точно должно быть в сервисном классе
    */
    protected function findLastRecording($sessionId)
    {
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
                return json_decode(Storage::get("video/$videoId/.recording.$videoId"), true);
            }
        }
        throw new OpenViduRecordingNotFoundException('This session hasn\'t unfinished recordings');
    }

    /**
     * @todo События сделать как положено
     * @todo Текущие соединения должны прописываться в кэше сессии
     * @param int $id 
     * @param Request $request
     * @param Openvidu $manager
     * @return \Illuminate\Http\Response
     */
    public function finish($id, Request $request, OpenVidu $manager) {
        $session = $manager->getSession($id);
        $lastRecordingId = $session->getLastRecordingId();
        $connectionsCount = intval($request->get('connectionsCount'));
        \broadcast(new VideoChatFinish($request->all(), ''));
        if (!($lastRecordingId && $connectionsCount<1)) {
            return response()->json(["message" => "Call finished, but there are still connections ($connectionsCount in total)"], 200);
        }        
        try {
            $recording = $manager->stopRecording($lastRecordingId);
        }
        catch(OpenViduRecordingNotFoundException $e) {
            $session->setIsBeingRecorded(false);
            $session->setLastRecordingId(null);
            return response()->json(['message'=>'Recording not found and thus unset'], 404);
        }            
        return response()->json(['recording' => $recording, 'message'=>'Recording successfully stopped'], 200);
    }
}
