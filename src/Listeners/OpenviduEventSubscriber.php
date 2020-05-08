<?php

namespace Sobolevna\LaravelVideoChat;

class UserEventSubscriber
{
    /**
     * Handle user login events.
     */
    public function handleSessionCreated($event) {

    }

    /**
     * Handle user logout events.
     */
    public function handleParticipantJoined($event) {
        
    }

    /**
     * Register the listeners for the subscriber.
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