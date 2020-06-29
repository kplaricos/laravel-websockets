<?php

namespace Kplaricos\LaravelWebSockets\Tests\Channels;

use Kplaricos\LaravelWebSockets\Tests\Mocks\Message;
use Kplaricos\LaravelWebSockets\Tests\TestCase;
use Kplaricos\LaravelWebSockets\WebSockets\Exceptions\InvalidSignature;

class PrivateChannelTest extends TestCase
{
    /** @test */
    public function clients_need_valid_auth_signatures_to_join_private_channels()
    {
        $this->expectException(InvalidSignature::class);

        $connection = $this->getWebSocketConnection();

        $message = new Message(json_encode([
            'event' => 'pusher:subscribe',
            'data' => [
                'auth' => 'invalid',
                'channel' => 'private-channel',
            ],
        ]));

        $this->pusherServer->onOpen($connection);

        $this->pusherServer->onMessage($connection, $message);
    }

    /** @test */
    public function clients_with_valid_auth_signatures_can_join_private_channels()
    {
        $connection = $this->getWebSocketConnection();

        $this->pusherServer->onOpen($connection);

        $signature = "{$connection->socketId}:private-channel";

        $hashedAppSecret = hash_hmac('sha256', $signature, $connection->app->secret);

        $message = new Message(json_encode([
            'event' => 'pusher:subscribe',
            'data' => [
                'auth' => "{$connection->app->key}:{$hashedAppSecret}",
                'channel' => 'private-channel',
            ],
        ]));

        $this->pusherServer->onMessage($connection, $message);

        $connection->assertSentEvent('pusher_internal:subscription_succeeded', [
            'channel' => 'private-channel',
        ]);
    }
}
