<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sobolevna\LaravelVideoChat\Tests\Services;

use Sobolevna\LaravelVideoChat\Models\{Conversation};
use Sobolevna\LaravelVideoChat\Tests\TestCase;
use Sobolevna\LaravelVideoChat\Tests\Helpers;
use Chat;

/**
 * Description of ChatTest
 * @coversDefaultClass Chat
 * @author sobolevna
 */
class ChatTest extends TestCase {
    
    protected $chat;
    
    public function setUp() : void 
    {
        parent::setUp();      
    }
    
    /**
     * @covers Chat::addParticipant
     * @dataProvider providerAddParticipant
     * @param string $conversationName
     * @param array $userData
     */
    public function testAddParticipant($conversationName, array $userData) {
        $user = Helpers\User::firstOrCreate($userData);
        $result = Chat::addParticipant($conversationName, $user->id);
        $this->assertNotNull($result);
        
        $conversation = Conversation::where('name', $conversationName)->first();
        $this->assertNotNull($conversation);
        $this->assertEquals($conversationName, $conversation->name);
        
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
                $this->makeUserData('user1')
            ]
        ];
    }
    
    protected function makeUserData($user) {
        return [
            'name' => $user,
            'email' => $user.'@test.com',
            'password' => $user,
        ];
    }
}
