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
class ConversationTest extends TestCase {
    
    /**
     * 
     */
    protected $user;

    public function setUp() : void 
    {
        parent::setUp();      
        $this->user = factory(Helpers\User::class)->create();
        $this->conversation = Conversation::create(['name'=>'conversation1']);
        $this->conversation->users()->attach($this->user->id);
    }
    
    /**
     * @covers ::index
     */
    public function testIndex() {
        $response= $this->actingAs($this->user, 'api')->getJson('/api/chat/conversations');
        
        $response->assertStatus(200);
        
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        $this->assertEquals($data['conversations'][0]['name'],$this->conversation->name);
    }

    /**
     * @covers ::store
     */
    public function testStore() {
        $conversationName = 'conversation2';
        $response= $this->actingAs($this->user, 'api')->postJson('/api/chat/conversations', ['name'=>$conversationName]);
        
        $response->assertStatus(201);
        $conversation = Conversation::with('users')->where('name', $conversationName)->first();

        $response->assertJson([
            'conversationId' => $conversation->id
        ]);

        $userId = $this->user->id;
        $this->assertTrue($conversation->users->filter(function($item) use ($userId) {
            return $item->id == $userId;
        })->isNotEmpty());
        
        $response = $this->actingAs($this->user, 'api')->getJson('/api/chat/conversations');
        
        $response->assertStatus(200);
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        
        $this->assertTrue(collect($data['conversations'])->filter(function($item) use ($conversationName ) {
            return $item['name'] == $conversationName ;
        })->isNotEmpty());
    }

    public function testShow() {
        $response= $this->actingAs($this->user, 'api')->getJson('/api/chat/conversations/'.$this->conversation->id);
        $response->assertStatus(200);
        $data = \json_decode($response->content(), true);
        $this->assertEquals($data['conversation']['name'],$this->conversation->name);   
    }

    public function testDestroyFail() {
        $response= $this->actingAs($this->user, 'api')->deleteJson('/api/chat/conversations/'.$this->conversation->id);
        $response->assertStatus(403);
        $response= $this->actingAs($this->user, 'api')->getJson('/api/chat/conversations');
        
        $response->assertStatus(200);
        
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        $this->assertEquals($data['conversations'][0]['name'],$this->conversation->name);        
    }

    public function testDestroySuccess() {
        $this->conversation->users()->detach();
        $response= $this->actingAs($this->user, 'api')->deleteJson('/api/chat/conversations/'.$this->conversation->id);
        $response->assertStatus(200);

        $response= $this->actingAs($this->user, 'api')->getJson('/api/chat/conversations');
        
        $response->assertStatus(200);
        
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        $this->assertEquals($data['conversations'],[]);
    }
    
}
