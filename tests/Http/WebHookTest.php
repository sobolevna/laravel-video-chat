<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sobolevna\LaravelVideoChat\Tests\Http;

use Sobolevna\LaravelVideoChat\Models\{Conversation, OpenviduEvent, OpenviduParticipant, Connection, Recording};
use Sobolevna\LaravelVideoChat\Http\Controllers\{ConversationController};
use Sobolevna\LaravelVideoChat\Tests\TestCase;
use Sobolevna\LaravelVideoChat\Tests\Helpers;
use Chat;

/**
 * 
 * @coversDefaultClass ConversationController
 * @author sobolevna
 */
class WebHookTest extends TestCase {
    
    /**
     * 
     */
    protected $user;

    public function setUp() : void 
    {
        parent::setUp();      
    }
    
    public function testSessionCreated() {
        $eventData = [
            'event' => 'sessionCreated',
            'sessionId' => '1',
            'timestamp' => date('U')*1000
        ];
        $response = $this->postJson('/api/chat/webhook', $eventData);
        $response->assertStatus(200);
        $log = OpenviduEvent::where('session_id', $eventData['sessionId'])->orderBy('id','desc')->first();
        $this->assertNotNull($log);
        $this->assertTrue($eventData['timestamp'] == json_decode($log->event_data)->timestamp);
    }

    public function testSessionDestroyed() {
        $eventData = [
            'event' => 'sessionCreated',
            'sessionId' => '1',
            'timestamp' => date('U')*1000,
            'startTime' => date('U')*1000 + 5000,
            'duration' => 5,
            'reason' => 'lastParticipantLeft'
        ];
        $response = $this->postJson('/api/chat/webhook', $eventData);
        $response->assertStatus(200);
        $log = OpenviduEvent::where('session_id', $eventData['sessionId'])->orderBy('id','desc')->first();
        $this->assertNotNull($log);
        $this->assertTrue($eventData['timestamp'] == json_decode($log->event_data)->timestamp);
    }

    public function testParticipantJoined() {
        $eventData = [
            'event' => 'participantJoined',
            'sessionId' => '1',
            'timestamp' => date('U')*1000,
            'participantId' => 'rwga',
            'platform' => 'test',
            'clientData' => json_encode([
                'userId' => 1
            ]),
            'serverData' => ''
        ];
        $response = $this->postJson('/api/chat/webhook', $eventData);
        $response->assertStatus(200);
        $participant = OpenviduParticipant::where('session_id', $eventData['sessionId'])->where('participant_id', $eventData['participantId'])->whereNull('reason')->first();
        $this->assertTrue(json_decode($eventData['clientData'])->userId == $participant->user_id);
    }
    
    public function testParticipantLeft() {
        $this->postJson('/api/chat/webhook', [
            'event' => 'participantJoined',
            'sessionId' => '1',
            'timestamp' => date('U')*1000,
            'participantId' => 'rwga',
            'platform' => 'test',
            'clientData' => json_encode([
                'userId' => 1
            ]),
            'serverData' => ''
        ]);
        $eventData = [
            'event' => 'participantLeft',
            'sessionId' => '1',
            'timestamp' => date('U')*1000,
            'participantId' => 'rwga',
            'platform' => 'test',
            'clientData' => json_encode([
                'userId' => 1
            ]),
            'serverData' => '',
            'startTime' => date('U')*1000 + 5000,
            'duration' => 5,
            'reason' => 'disconnect'
        ];
        $response = $this->postJson('/api/chat/webhook', $eventData);
        $response->assertStatus(200);
        $participant = OpenviduParticipant::where('session_id', $eventData['sessionId'])->where('participant_id', $eventData['participantId'])->first();
        $this->assertNull($participant);
        $deletedParticipant = OpenviduParticipant::withTrashed()->where('session_id', $eventData['sessionId'])->where('participant_id', $eventData['participantId'])->first();
        $this->assertTrue(json_decode($eventData['clientData'])->userId == $deletedParticipant->user_id);
        $this->assertTrue($eventData['reason'] == $deletedParticipant->reason);
    }

    public function testWebrtcConnectionCreated() {
        $eventData = [
            'event' => 'webrtcConnectionCreated',
            'sessionId' => '1',
            'timestamp' => date('U')*1000,
            'participantId' => 'rwga',
            'platform' => 'test',
            'connection' => "INBOUND",
            'receivingFrom' => 'gresge',
            'audioEnabled'=>true,
            'videoEnabled'=>true,
            'videoSource'=>"CAMERA",
            'videoFramerate'=>30,
            'videoDimensions'=>"1920x1080",
        ];
        $response = $this->postJson('/api/chat/webhook', $eventData);
        $response->assertStatus(200);
        $connection = Connection::where('session_id', $eventData['sessionId'])->where('participant_id', $eventData['participantId'])->first();
        $this->assertNotNull($connection);
    }


    public function testWebrtcConnectionDestroyed() {
        $this->testWebrtcConnectionCreated();
        $eventData = [
            'event' => 'webrtcConnectionDestroyed',
            'sessionId' => '1',
            'timestamp' => date('U')*1000,
            'participantId' => 'rwga',
            'platform' => 'test',
            'connection' => "INBOUND",
            'receivingFrom' => 'gresge',
            'audioEnabled'=>true,
            'videoEnabled'=>true,
            'videoSource'=>"CAMERA",
            'videoFramerate'=>30,
            'videoDimensions'=>"1920x1080",
            'startTime' => date('U')*1000 + 5000,
            'duration' => 5,
            'reason' => 'disconnect'
        ];
        $response = $this->postJson('/api/chat/webhook', $eventData);
        $response->assertStatus(200);
        $connection = Connection::where('session_id', $eventData['sessionId'])->where('participant_id', $eventData['participantId'])->first();
        $this->assertNull($connection);
        $deletedConnection = Connection::withTrashed()->where('session_id', $eventData['sessionId'])->where('participant_id', $eventData['participantId'])->first();
        $this->assertTrue($eventData['reason'] == $deletedConnection->reason);
    }

    /**
     * @dataProvider providerRecordingStatusChanged
     */
    public function testRecordingStatusChanged($eventData) {
        $response = $this->postJson('/api/chat/webhook', $eventData);
        $response->assertStatus(200);
        $recording = Recording::where('recording_id', $eventData['id'])->first();
        $this->assertNotNull($recording);
        if ($eventData['status'] == 'ready') {
            $this->assertEquals($recording->url, "/openvidu/recordings/".$eventData['id']."/".$eventData['name'].".mp4");
        }
    }

    public function providerRecordingStatusChanged() {
        $basic = [
            'event' => 'recordingStatusChanged',
            'sessionId' => '1',
            'timestamp' => date('U')*1000,
            'startTime' => date('U')*1000,
            'id' => '1',
            'name' => '1',
            'outputMode' => 'COMPOSED',
            'hasAudio' => true,
            'hasVideo' => true,
            'recordingLayout' => 'BEST_FIT',
            'resolution' => "1280x720",            
        ];
        $start = [
            'status'=>'started',
        ];
        $stop = [
            'size' => 1000000,
            'duration' => 5,
            'status'=>'stopped',
            'reason'=>'automaticStop'
        ];
        $fail = [
            'status'=>'failed',
        ];
        $ready = [
            'size' => 1000000,
            'duration' => 5,
            'status'=>'ready',
            'reason'=>'automaticStop'
        ];
        return [
            'start' => [array_merge($basic, $start)],
            'stop' => [array_merge($basic, $stop)],
            'fail' => [array_merge($basic, $fail)],
            'ready' => [array_merge($basic, $ready)]
        ];
    } 

    /**
     * @dataProvider providerCommonErrors
     */
    public function testCommonErrors($eventData) {
        $response = $this->postJson('/api/chat/webhook', $eventData);
        $response->assertStatus(200);
    }

    public function providerCommonErrors() {
        return [
            'participantLeft_1589893664531' => [
                [
                    "sessionId"=> "1",
                    "timestamp"=> "1589893664531",
                    "startTime"=> "1589893540238",
                    "duration"=> "124",
                    "reason"=> "disconnect",
                    "participantId"=> "con_A8XdKgCrSP",
                    "location"=> "unknown",
                    "platform"=> "Firefox 76.0 on Ubuntu 64-bit",
                    "clientData"=> "{\"userId\"=>1}",
                    "serverData"=> "",
                    "event"=> "participantLeft"
                ]    
            ]
        ];
    }
}
