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
    public function start($id, Request $request, Openvidu $manager) {
        $session = $manager->getSession($id);
        \broadcast(new VideoChatStart($request->all(), ''));
        if (!$session->isBeingRecorded()) {
            $recording = $manager->startRecording(RecordingPropertiesBuilder::build($request->all()));
            return response()->json(['recording' => $recording], 200);
        }
        return response()->json(["message' => 'Call started with session already being recorded"], 200);
    }

    /**
     * @todo События сделать как положено
     * @todo Текущие соединения должны прописываться в кэше сессии
     * @param int $id 
     * @param Request $request
     * @param Openvidu $manager
     * @return \Illuminate\Http\Response
     */
    public function finish($id, Request $request, Openvidu $manager) {
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
