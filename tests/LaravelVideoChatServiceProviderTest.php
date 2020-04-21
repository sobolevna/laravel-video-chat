<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 11/10/17
 * Time: 1:20 AM.
 */

namespace Sobolevna\LaravelVideoChat\Tests;

use Sobolevna\LaravelVideoChat\Services\Chat;
use Illuminate\Support\Facades\Route;

class LaravelVideoChatServiceProviderTest extends TestCase
{

    public function testFacades()
    {
        $this->assertTrue(class_exists(\Chat::class));
    }

    public function testConfig() {
        $this->assertNotEmpty(\config('laravel-video-chat'));
    }

    public function testMigrations() {
        foreach (\config('laravel-video-chat.table') as $table) {
            $this->assertTrue(\Schema::hasTable($table));
        }
        
    }

    public function testRoutes() {
        $this->assertTrue(Route::has('api.chat.conversations.index'));
        $this->assertTrue(Route::has('api.chat.conversations.participants.index'));
        $this->assertTrue(Route::has('api.chat.conversations.messages.index'));
        $this->assertTrue(Route::has('api.chat.conversations.files.index'));
    }
}
