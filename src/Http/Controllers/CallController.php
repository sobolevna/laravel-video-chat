<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Models;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Sobolevna\LaravelVideoChat\Events\{
    VideoChatStart, VideoChatFinish
};
use Illuminate\Routing\Controller;
use SquareetLabs\LaravelOpenVidu\OpenVidu;
use SquareetLabs\LaravelOpenVidu\Exceptions\{OpenViduRecordingNotFoundException, OpenViduSessionCantRecordingException};
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
        if (!config('laravel-video-chat.recording')) {
            return response()->json(["message" => "Call successfully started"], 200);
        }
        if (!$session->isBeingRecorded()) {
            try{ 
                $recording = $manager->startRecording(RecordingPropertiesBuilder::build($request->all()));         
                $message = "Recording successfully started";
            }
            catch(OpenViduSessionCantRecordingException $e) {
                $session->setIsBeingRecorded(true);
                $message = "Session has been recorded, but there wasn't any data of it";              
            }
            return response()->json(["message" => $message], 200);       
        }
        return response()->json(["message" => "Call started with session already being recorded"], 200);
    }

    /**
     * @todo Вот это точно должно быть в сервисном классе
    */
    protected function findLastRecording($sessionId)
    {
        return Models\Recording::where('session_id', $sessionId)->orderBy('id', 'desc')->first();
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
        if (!config('laravel-video-chat.recording')) {
            return response()->json(["message" => "Call successfully stopped"], 200);
        }
        $session = $manager->getSession($id);
        $lastActiveRecording = Models\Recording::where('session_id', $id)->where('status', 'started')->orderBy('id', 'desc')->first();
        $connections = Models\Connection::where('session_id', $id)->get();
        $connectionsCount = $connections->count();
        \broadcast(new VideoChatFinish($request->all(), ''));
        if ($connectionsCount > 2) {
            return response()->json(["message" => "Call finished, but there are still connections ($connectionsCount in total)"], 200);
        }        
        if (!$lastActiveRecording) {
            $session->setIsBeingRecorded(false);
            return response()->json(['message'=>'Recording not found and thus unset'], 404);
        }
        try {
            $recording = $manager->stopRecording($lastRecording->recording_id);
        }
        catch(OpenViduRecordingNotFoundException $e) {
            $session->setIsBeingRecorded(false);
            $session->setLastRecordingId(null);
            return response()->json(['message'=>'Recording not found and thus unset'], 404);
        }            
        return response()->json(['recording' => $recording, 'message'=>'Recording successfully stopped'], 200);
    }
}
