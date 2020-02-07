<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sobolevna\LaravelVideoChat\Tests;

use Sobolevna\LaravelVideoChat\Models\{Conversation};
//use Sobolevna\LaravelVideoChat\Repositories\ConversationRepository;
use Chat;

/**
 * Description of ChatTest
 *
 * @author sobolevna
 */
class ChatTest extends TestCase {
    
    protected $chat;
    
    public function setUp() : void 
    {
        parent::setUp();
        //$this->chat = new \Sobolevna\LaravelVideoChat\Services\Chat(\config(), new ConversationRepository);        
    }
    
    /**
     * @dataProvider providerAddParticipant
     * @param string $conversationName
     * @param array $userData
     */
    public function testAddParticipant($conversationName, array $userData) {
        $result = Chat::addParticipant($conversationName, $userData);
        $this->assertTrue($result);
        
        $conversation = Conversation::where('name', $conversationName)->first();
        $this->assertNotNull($conversation);
        $this->assertEquals($conversationName, $conversation->name);
        
        $user = config('laravel-video-chat.user.model')::where('name', $userData['name'])->first();
        $this->assertNotNull($user);
        $this->assertEquals($userData['name'], $user->name);
        
        $userCount = $conversation->users()->get()->count();
        $this->assertTrue($userCount > 0);
        $this->assertNotNull($conversation->whereHas('users', function($query) use ($user){
            $query->where('users.id', $user->id);
        })->first());
    }
    
    public function providerAddParticipant() {
        return [
            [
                'conversation1',
                [
                    'name' => 'user1'
                ]
            ]
        ];
    }
}
