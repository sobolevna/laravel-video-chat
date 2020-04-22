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
use Illuminate\Http\UploadedFile;
use Chat;

/**
 * 
 * @coversDefaultClass MessageController
 * @author sobolevna
 */
class FileTest extends TestCase {
    
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
        $file = UploadedFile::fake()->create('test.txt', 1);
        $this->file = Chat::saveFile($this->conversation, $file, $this->user->id, $this->message->id);
        $this->baseUrl = '/api/chat/conversations/'.$this->conversation->id.'/files';
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
        $this->assertEquals($data['files'][0]['id'],$this->file->id);
    }

    /**
     * @covers ::store
     */
    public function testStore() {
        $file = UploadedFile::fake()->create('test2.txt', 2);
        $response= $this->actingAs($this->user, 'api')
            ->postJson($this->baseUrl, [
                'files'=>[$file], 
                'messageId'=>$this->message->id
            ]);
        
        $response->assertStatus(201);
        $this->assertTrue($this->conversation->files->filter(function($item) use ($file){
            return stripos($item->name, $file->name) !== false;
        })->isNotEmpty());
        
        $response = $this->actingAs($this->user, 'api')->getJson($this->baseUrl);
        
        $response->assertStatus(200);
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content());
        $this->assertTrue(collect($data->files)->filter(function($item) use ($file){
            return stripos($item->name, $file->name) !== false;
        })->isNotEmpty());
    }

    public function testDestroyFail() {
        $newUser = factory(Helpers\User::class)->create(); 
        $this->conversation->users()->attach($newUser->id);
        $message = $this->conversation->messages()->create([
            'user_id'=>$newUser->id,
            'text' =>'Another text'
        ]);
        $file = UploadedFile::fake()->create('testDestroy.txt', 2);
        $newFile = Chat::saveFile($this->conversation, $file, $newUser->id, $this->message->id);
        $id = $newFile->id;
        
        $response= $this->actingAs($this->user, 'api')->deleteJson($this->baseUrl.'/'.$id);
        $response->assertStatus(403);

        $response= $this->actingAs($this->user, 'api')->getJson($this->baseUrl);
        
        $response->assertStatus(200);
        
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        $this->assertTrue(collect($data['files'])->filter(function($item) use ($id) {
            return $item['id'] == $id;
        })->isNotEmpty());
    }

    public function testDestroySuccess() {
        $id = $this->file->id;
        $response= $this->actingAs($this->user, 'api')->deleteJson($this->baseUrl.'/'.$id);
        $response->assertStatus(200);

        $response= $this->actingAs($this->user, 'api')->getJson($this->baseUrl);
        
        $response->assertStatus(200);
        
        /**
         * @todo Найти способ заставить работать assertJsonPath
         */
        $data = \json_decode($response->content(), true);
        
        $this->assertTrue(collect($data['files'])->filter(function($item) use ($id) {
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
