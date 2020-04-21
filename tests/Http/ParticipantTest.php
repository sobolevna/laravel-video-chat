<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sobolevna\LaravelVideoChat\Tests\Http;

use Sobolevna\LaravelVideoChat\Models\{Conversation};
use Sobolevna\LaravelVideoChat\Http\Controllers\{ConversationController};
use Sobolevna\LaravelVideoChat\Tests\TestCase;
use Sobolevna\LaravelVideoChat\Tests\Helpers;
use Chat;

/**
 * 
 * @coversDefaultClass ConversationController
 * @author sobolevna
 */
class ParticipantTest extends TestCase {
    
    /**
     * @var Helpers\User
     */
    protected $user;

    protected $baseUrl;

    public function setUp() : void 
    {
        parent::setUp();      
        $this->user = factory(Helpers\User::class)->create();
        $this->conversation = Conversation::create(['name'=>'conversation1']);
        $this->conversation->users()->attach($this->user->id);
        $this->baseUrl = '/api/chat/conversations/'.$this->conversation->id.'/participants';
    }
    
    /**
     * @covers ::index
     */
    public function testIndex() {
        $response= $this->actingAs($this->user, 'api')->getJson($this->baseUrl);
        
        $response->assertStatus(200);
        
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        $this->assertEquals($data['participants'][0]['id'],$this->user->id);
    }

    /**
     * @covers ::store
     */
    public function testStore() {
        $newUser = factory(Helpers\User::class)->create();        
        $response= $this->actingAs($this->user, 'api')->postJson($this->baseUrl, ['users'=>[$newUser->id]]);
        
        $response->assertStatus(201);
        $userId = $newUser->id;
        $this->assertTrue($this->conversation->users->filter(function($item) use ($userId) {
            return $item->id == $userId;
        })->isNotEmpty());
        
        $response = $this->actingAs($this->user, 'api')->getJson($this->baseUrl);
        
        $response->assertStatus(200);
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        
        $this->assertTrue(collect($data['participants'])->filter(function($item) use ($userId) {
            return $item['id'] == $userId;
        })->isNotEmpty());
    }

    public function testDestroyFail() {
        $newUser = $newUser = factory(Helpers\User::class)->create(); 
        $userId = $newUser->id;
        $this->conversation->users()->attach($newUser->id);
        $response= $this->actingAs($this->user, 'api')->deleteJson($this->baseUrl.'/'.$userId);
        $response->assertStatus(403);

        $response= $this->actingAs($this->user, 'api')->getJson($this->baseUrl);
        
        $response->assertStatus(200);
        
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        $this->assertTrue(collect($data['participants'])->filter(function($item) use ($userId) {
            return $item['id'] == $userId;
        })->isNotEmpty());
    }

    public function testDestroySuccess() {
        $userId = $this->user->id;
        $response= $this->actingAs($this->user, 'api')->deleteJson($this->baseUrl.'/'.$userId);
        $response->assertStatus(200);

        $response= $this->actingAs($this->user, 'api')->getJson($this->baseUrl);
        
        $response->assertStatus(200);
        
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        
        $this->assertTrue(collect($data['participants'])->filter(function($item) use ($userId) {
            return $item['id'] == $userId;
        })->isEmpty());
    }
    
    protected function makeUserData($user) {
        return [
            'name' => $user,
            'email' => $user.'@test.com',
            'password' => $user,
        ];
    }
}
