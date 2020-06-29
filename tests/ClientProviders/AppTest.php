<?php

namespace Kplaricos\LaravelWebSockets\Tests\ClientProviders;

use Kplaricos\LaravelWebSockets\Apps\App;
use Kplaricos\LaravelWebSockets\Exceptions\InvalidApp;
use Kplaricos\LaravelWebSockets\Tests\TestCase;

class AppTest extends TestCase
{
    /** @test */
    public function it_can_create_a_client()
    {
        new App(1, 'appKey', 'appSecret', 'new');

        $this->markTestAsPassed();
    }

    /** @test */
    public function it_will_not_accept_an_empty_appKey()
    {
        $this->expectException(InvalidApp::class);

        new App(1, '', 'appSecret', 'new');
    }

    /** @test */
    public function it_will_not_accept_an_empty_appSecret()
    {
        $this->expectException(InvalidApp::class);

        new App(1, 'appKey', '', 'new');
    }
}
