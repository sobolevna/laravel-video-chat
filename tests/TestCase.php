<?php
/**
 * Created by PhpStorm.
 * User: nyinyilwin
 * Date: 11/10/17
 * Time: 1:18 AM.
 */

namespace Sobolevna\LaravelVideoChat\Tests;

use Orchestra\Testbench\TestCase as VendorTestCase;
use Sobolevna\LaravelVideoChat\LaravelVideoChatServiceProvider;

abstract class TestCase extends VendorTestCase
{
    /**
     * Get the service provider class.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return string
     */
    protected function getPackageProviders($app)
    {
        return [LaravelVideoChatServiceProvider::class];
    }
    
    /**
     * Setup DB before each test.
     *
     * @return void  
     */
    public function setUp() : void
    { 
        parent::setUp();
        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->withFactories(__DIR__.'/database/factories');
    }

    protected function getPackageAliases($app)
    {
        return [
            
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        //$app['config']->set('filesystems.disks.local.root', __DIR__.'/storage/app/');
        //$app['config']->set('filesystems.disks.local.root', __DIR__.'/storage/app/public/');
        $app['config']->set('laravel-video-chat.user.model', Helpers\User::class);
        $app['config']->set('logging.default', 'single');
        $app['config']->set('logging.channels.single.path', __DIR__.'/storage/logs/laravel.log');
    }
}
