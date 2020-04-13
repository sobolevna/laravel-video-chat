<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Illuminate\Routing\Controller;
use SquareetLabs\LaravelOpenVidu\OpenVidu;
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
        if (!$session->getLastRecordingId()) {
            $recording = $manager->startRecording(RecordingPropertiesBuilder::build($request->all()));
            return response()->json(['recording' => $recording], 200);
        }
    }

    /**
     * @todo События сделать как положено
     * @param int $id 
     * @param Request $request
     * @param Openvidu $manager
     * @return \Illuminate\Http\Response
     */
    public function finish($id, Request $request, Openvidu $manager) {
        $session = $manager->getSession($id);
        $lastRecordingId = $session->getLastRecordingId();
        $connections = $session->getActiveConnections();
        \broadcast(new VideoChatFinish($request->all(), ''));
        if ($lastRecordingId && count($connections)<3) {
            $recording = $manager->stopRecording($lastRecordingId);
            return response()->json(['recording' => $recording], 200);
        }
    }
}
