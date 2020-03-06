<?php

namespace Sobolevna\LaravelVideoChat;

use Dflydev\ApacheMimeTypes\PhpRepository;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Repositories\ConversationRepository;
use Sobolevna\LaravelVideoChat\Services\Chat as ChatService;
use Sobolevna\LaravelVideoChat\Services\UploadManager;
use Illuminate\Support\Facades\Route;

class LaravelVideoChatServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            $this->configPath()     => config_path('laravel-video-chat.php'),
            $this->componentsPath() => base_path('resources/js/components/laravel-video-chat'),
            __DIR__.'/../resources/js/openvidu-app.js' => base_path('resources/js/openvidu-app.js'),
            __DIR__.'/../resources/js/views/laravel-video-chat' => base_path('resources/js/views/laravel-video-chat'),
            __DIR__.'/../resources/js/router/laravel-video-chat.js' => base_path('resources/js/router/laravel-video-chat.js'),
        ]);

        $this->loadMigrationsFrom($this->migrationsPath());
        $this->registerBroadcast();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath(), 'laravel-video-chat');
        $this->registerFacade();
        $this->registerChat();
        $this->registerUploadManager();
        $this->registerAlias();
        $this->registerRoutes();
    }

    protected function registerFacade()
    {
        $this->app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Chat', Chat::class);
        });
    }

    protected function registerUploadManager()
    {
        $this->app->singleton('upload.manager', function ($app) {
            $mime = $app[PhpRepository::class];
            $config = $app['config'];

            return new UploadManager($config, $mime);
        });
        $this->app->alias('upload.manager', UploadManager::class);
    }

    protected function registerChat()
    {
        $this->app->bind('chat', function ($app) {
            $config = $app['config'];
            $conversation = $app['conversation.repository'];

            return new ChatService($config, $conversation);
        });
    }

    protected function registerAlias()
    {
        $this->app->singleton('conversation.repository', function ($app) {
            $manger = $app['upload.manager'];

            return new ConversationRepository($manger);
        });
        $this->app->alias('conversation.repository', ConversationRepository::class);        
    }

    protected function registerBroadcast()
    {
        Broadcast::channel(
            $this->app['config']->get('laravel-video-chat.channel.chat_room').'-{conversationId}',
            function ($user, $conversationId) {
                if ($this->app['conversation.repository']->canJoinConversation($user, $conversationId)) {
                    return $user;
                }
            }
        );

    }
    
    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__.'/Http/routes.php');
        });
    }

    /**
     * Get the SmsUp route group configuration array.
     *
     * @return array
     */
    private function routeConfiguration()
    {
        return [
            'domain' => null,
            
        ];
    }

    /**
     * @return string
     */
    protected function configPath()
    {
        return __DIR__.'/../config/laravel-video-chat.php';
    }

    /**
     * @return string
     */
    protected function migrationsPath()
    {
        return __DIR__.'/../database/migrations';
    }

    /**
     * @return string
     */
    protected function componentsPath()
    {
        return  __DIR__.'/../resources/js/components/laravel-video-chat';
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'conversation.repository',
            'upload.manager',
        ];
    }
}
