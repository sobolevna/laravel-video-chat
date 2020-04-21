<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sobolevna\LaravelVideoChat\Tests\Http;

use Sobolevna\LaravelVideoChat\Models\{Conversation};
use Sobolevna\LaravelVideoChat\Http\Controllers\{MessageController};
use Sobolevna\LaravelVideoChat\Tests\TestCase;
use Sobolevna\LaravelVideoChat\Tests\Helpers;
use Chat;

/**
 * 
 * @coversDefaultClass MessageController
 * @author sobolevna
 */
class MessageTest extends TestCase {
    
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
        $this->message = $this->conversation->messages()->create([
            'conversation_id'=>$this->conversation->id,
            'user_id'=> $this->user->id,
            'text'=> 'Some text'
        ]);
        
        $this->baseUrl = '/api/chat/conversations/'.$this->conversation->id.'/messages';
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
        $this->assertEquals($data['messages'][0]['id'],$this->message->id);
    }

    /**
     * @covers ::store
     */
    public function testStore() {
        $response= $this->actingAs($this->user, 'api')->postJson($this->baseUrl, ['text'=>'Another text']);
        
        $response->assertStatus(201);
        $this->assertTrue($this->conversation->users->filter(function($item){
            return $item->text == 'Another text';
        })->isNotEmpty());
        
        $response = $this->actingAs($this->user, 'api')->getJson($this->baseUrl);
        
        $response->assertStatus(200);
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        
        $this->assertTrue(collect($data['messages'])->filter(function($item) use ($userId) {
            return $item['id'] == 'Another text';
        })->isNotEmpty());
    }

    public function testDestroyFail() {
        $newUser = factory(Helpers\User::class)->create(); 
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
            return $item['id'] == $id;
        })->isNotEmpty());
    }

    public function testDestroySuccess() {
        $id = $this->message->id;
        $response= $this->actingAs($this->user, 'api')->deleteJson($this->baseUrl.'/'.$id);
        $response->assertStatus(200);

        $response= $this->actingAs($this->user, 'api')->getJson($this->baseUrl);
        
        $response->assertStatus(200);
        
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        
        $this->assertTrue(collect($data['messages'])->filter(function($item) use ($id) {
            return $item['id'] == $id;
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
