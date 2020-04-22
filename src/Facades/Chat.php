<?php

namespace Sobolevna\LaravelVideoChat\Facades;

use Illuminate\Support\Facades\Facade;
use Sobolevna\LaravelVideoChat\Facades\Services\Chat as ChatService;

/**
 * @see ChatService
 */
class Chat extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'chat';
    }
}
