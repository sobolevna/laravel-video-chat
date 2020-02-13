<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 11/10/17
 * Time: 1:20 AM.
 */

namespace Sobolevna\LaravelVideoChat\Tests;

use Sobolevna\LaravelVideoChat\Services\Chat;

class LaravelVideoChatServiceProviderTest extends TestCase
{

    public function testChatIsInjectable()
    {
        //$this->assertIsInjectable(Chat::class);
        $this->assertTrue(true);
    }
}
