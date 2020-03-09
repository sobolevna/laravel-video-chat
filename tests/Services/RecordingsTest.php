<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Sobolevna\LaravelVideoChat\Tests\Services;

use Sobolevna\LaravelVideoChat\Services\Recordings;
use Sobolevna\LaravelVideoChat\Tests\TestCase;
use Storage;

/**
 * Description of ChatTest
 * @coversDefaultClass ChatController
 * @author sobolevna
 */
class RecordingsTest extends TestCase {
    
    protected $recordings;
    
    public function setUp() : void 
    {
        parent::setUp(); 
        $this->recordings = new Recordings;

        Storage::makeDirectory('video');
        Storage::put('video/1/1.mp4', '');
        Storage::put('video/1/1.jpg', '');
        Storage::put('video/1-1/1-1.mp4', '');
        Storage::put('video/1-2/1-2.mp4', '');
        Storage::put('video/1-2/1-2.jpg', '');
    }

    public function tearDown() :void {
        Storage::deleteDirectory('video');
    }
    
    /**
     * @covers ::recordings
     */
    public function testRecordings() {
        $videos = $this->recordings->recordings(1);
        $this->assertTrue(count($videos) == 2);
    }
}
