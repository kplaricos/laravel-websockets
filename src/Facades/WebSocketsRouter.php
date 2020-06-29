<?php

namespace Kplaricos\LaravelWebSockets\Facades;

use Illuminate\Support\Facades\Facade;

/** @see \Kplaricos\LaravelWebSockets\Server\Router */
class WebSocketsRouter extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'websockets.router';
    }
}
