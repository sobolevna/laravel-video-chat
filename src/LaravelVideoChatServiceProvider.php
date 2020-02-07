<?php

namespace Sobolevna\LaravelVideoChat;

use Dflydev\ApacheMimeTypes\PhpRepository;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;
use Sobolevna\LaravelVideoChat\Facades\Chat;
use Sobolevna\LaravelVideoChat\Repositories\ConversationRepository;
use Sobolevna\LaravelVideoChat\Services\Chat as ChatService;
use Sobolevna\LaravelVideoChat\Services\UploadManager;

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
            $this->componentsPath() => base_path('resources/assets/js/components/laravel-video-chat'),
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
        return  __DIR__.'/../resources/assets/js/components';
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
