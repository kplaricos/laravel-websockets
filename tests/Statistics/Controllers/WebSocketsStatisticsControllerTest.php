<?php

namespace Kplaricos\LaravelWebSockets\Tests\Statistics\Controllers;

use Kplaricos\LaravelWebSockets\Statistics\Http\Controllers\WebSocketStatisticsEntriesController;
use Kplaricos\LaravelWebSockets\Statistics\Models\WebSocketsStatisticsEntry;
use Kplaricos\LaravelWebSockets\Tests\TestCase;

class WebSocketsStatisticsControllerTest extends TestCase
{
    /** @test */
    public function it_can_store_statistics()
    {
        $this->post(
            action([WebSocketStatisticsEntriesController::class, 'store']),
            array_merge($this->payload(), [
                'secret' => config('websockets.apps.0.secret'),
            ])
        );

        $entries = WebSocketsStatisticsEntry::get();

        $this->assertCount(1, $entries);

        $this->assertArrayHasKey('app_id', $entries->first()->attributesToArray());
    }

    protected function payload(): array
    {
        return [
            'app_id' => config('websockets.apps.0.id'),
            'peak_connection_count' => 1,
            'websocket_message_count' => 2,
            'api_message_count' => 3,
        ];
    }
}
