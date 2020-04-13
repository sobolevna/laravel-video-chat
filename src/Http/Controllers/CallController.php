<?php

namespace Sobolevna\LaravelVideoChat\Http\Controllers;

use Illuminate\Http\Request;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Services\Recordings;
use Illuminate\Routing\Controller;
use SquareetLabs\LaravelOpenVidu\OpenVidu;
use SquareetLabs\LaravelOpenVidu\Builders\RecordingPropertiesBuilder;
use Storage;

class CallController extends Controller
{
    protected $openviduController = '\SquareetLabs\LaravelOpenVidu\Http\Controllers\OpenViduController';
    
    public function start($id, Request $request, Openvidu $manager) {
        $session = $manager->getSession($id);
        if (!$session->getLastRecordingId()) {
            $recording = $manager->startRecording(RecordingPropertiesBuilder::build($request->all()));
            return response()->json(['recording' => $recording], 200);
        }
    }

    public function finish($id, Request $request, Openvidu $manager) {
        $session = $manager->getSession($id);
        $lastRecordingId = $session->getLastRecordingId();
        if ($lastRecordingId) {
            $recording = $manager->startRecording($lastRecordingId);
            return response()->json(['recording' => $recording], 200);
        }
    }
}
