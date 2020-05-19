<?php

namespace Sobolevna\LaravelVideoChat\Listeners;

use SquareetLabs\LaravelOpenVidu\Events;
use Sobolevna\LaravelVideoChat\Models;

class OpenviduEventSubscriber
{
    protected function logEvent($event) {
        return Models\OpenviduEvent::create([
            'session_id'=>$event->sessionId,
            'event_name'=> $event->event,
            'event_data' => json_encode($event)
        ]);
    }

    protected function getEventName($event) {
        return lcfirst((new \ReflectionClass($event))->getShortName());
    }

    protected function getEventData($event) {
        $log = $this->logEvent($event);
        $data = [];
        foreach ($event as $key => $value) {
            $data[\Str::snake($key)] = $value;
        }
        try {
            $clientData = json_decode($event->clientData);
            if ($clientData) {
                $userId = json_decode($event->clientData)->userId;
            }
            else {
                $userId = null;
            }
        }
        catch(\Exception $e) {
            $userId = null;
        }
        $data['user_id'] = $userId;
        $data['event_id'] = $log->id;
        $data['start_time'] = !empty($data['start_time']) ? date('Y-m-d H:i:s', $data['start_time'] / 1000) : null;
        return $data;
    }

    /**
     * Handle user login events.
     */
    public function handleSessionCreated($event) {
        $this->logEvent($event);
    }

    /**
     * Handle user login events.
     */
    public function handleSessionDestroyed($event) {
        $this->logEvent($event);
    }

    /**
     * Handle user logout events.
     */
    public function handleParticipantJoined($event) {
        $data = $this->getEventData($event);
        Models\OpenviduParticipant::create($data);
    }

    /**
     * Handle user logout events.
     */
    public function handleParticipantLeft($event) {
        $data = $this->getEventData($event);
        $participant = Models\OpenviduParticipant::where('session_id', $data['session_id'])->where('participant_id', $data['participant_id'])->first();
        if (!$participant) {
            $participant = Models\OpenviduParticipant::create($data);
        }
        else {
            $participant->update($data);
            $participant->save();
        }
        $participant->delete();
    }

    public function handleWebrtcConnectionCreated($event) {
        $data = $this->getEventData($event);
        $participant = Models\OpenviduParticipant::where('session_id', $data['session_id'])->where('participant_id', $data['participant_id'])->first();
        $data['user_id'] = $participant ? $participant->user_id : null;
        Models\Connection::create($data);
    }

    public function handleWebrtcConnectionDestroyed($event) {
        $data = $this->getEventData($event);
        $connection = Models\Connection::where('session_id', $data['session_id'])->where('participant_id', $data['participant_id'])->first();
        if (!$connection) {
            $connection = Models\Connection::create($data);
        }
        else {
            $connection->update($data);
            $connection->save();
        }        
        $connection->delete();
    }

    public function handleRecordingStatusChanged($event) {
        $data = $this->getEventData($event);
        $data['recording_id'] = $data['id'];
        $data['url'] = $data['status'] == 'ready' ? "/openvidu/recordings/".$data['recording_id']."/".$data['name'].".mp4" : null;
        $recording = Models\Recording::where('recording_id', $data['id'])->first();
        if ($recording) {
            $recording->update($data);
        }
        else {
            $recording = Models\Recording::create($data);
        }
    }

    /**
     * Register the listeners for the subscriber.
     * @todo add event filterEventDispatched
     * 
     * @param  \Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $class = self::class;
        $namespace = 'SquareetLabs\LaravelOpenVidu\Events\\';
        $eventNames = [
            'SessionCreated',
            'ParticipantJoined',
            'ParticipantLeft',
            'RecordingStatusChanged',
            'SessionDestroyed',
            'SessionDestroyed',
            'WebRTCConnectionCreated',
            'WebRTCConnectionDestroyed',
        ];
        foreach ($eventNames as $eventName) {
            if (\method_exists($this, 'handle'.$eventName) && class_exists($namespace.$eventName)) {
                $events->listen(
                    $namespace.$eventName,
                    $class.'@handle'.$eventName
                );
            }
        }
        
        
    }
}