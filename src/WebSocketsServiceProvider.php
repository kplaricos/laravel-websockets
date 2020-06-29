<?php

namespace Kplaricos\LaravelWebSockets;

use Kplaricos\LaravelWebSockets\Apps\AppProvider;
use Kplaricos\LaravelWebSockets\Dashboard\Http\Controllers\AuthenticateDashboard;
use Kplaricos\LaravelWebSockets\Dashboard\Http\Controllers\DashboardApiController;
use Kplaricos\LaravelWebSockets\Dashboard\Http\Controllers\SendMessage;
use Kplaricos\LaravelWebSockets\Dashboard\Http\Controllers\ShowDashboard;
use Kplaricos\LaravelWebSockets\Dashboard\Http\Middleware\Authorize as AuthorizeDashboard;
use Kplaricos\LaravelWebSockets\Server\Router;
use Kplaricos\LaravelWebSockets\Statistics\Http\Controllers\WebSocketStatisticsEntriesController;
use Kplaricos\LaravelWebSockets\Statistics\Http\Middleware\Authorize as AuthorizeStatistics;
use Kplaricos\LaravelWebSockets\WebSockets\Channels\ChannelManager;
use Kplaricos\LaravelWebSockets\WebSockets\Channels\ChannelManagers\ArrayChannelManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class WebSocketsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/websockets.php' => base_path('config/websockets.php'),
        ], 'config');

        if (!class_exists('CreateWebSocketsStatisticsEntries')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_websockets_statistics_entries_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_websockets_statistics_entries_table.php'),
            ], 'migrations');
        }

        $this
            ->registerRoutes()
            ->registerDashboardGate();

        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'websockets');

        $this->commands([
            Console\StartWebSocketServer::class,
            Console\CleanStatistics::class,
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/websockets.php', 'websockets');

        $this->app->singleton('websockets.router', function () {
            return new Router();
        });

        $this->app->singleton(ChannelManager::class, function () {
            return config('websockets.channel_manager') !== null && class_exists(config('websockets.channel_manager'))
                ? app(config('websockets.channel_manager')) : new ArrayChannelManager();
        });

        $this->app->singleton(AppProvider::class, function () {
            return app(config('websockets.app_provider'));
        });
    }

    protected function registerRoutes()
    {
        Route::prefix(config('websockets.path'))->group(function () {
            Route::middleware(config('websockets.middleware', [AuthorizeDashboard::class]))->group(function () {
                Route::get('/', ShowDashboard::class);
                Route::get('/api/{appId}/statistics', [DashboardApiController::class,  'getStatistics']);
                Route::post('auth', AuthenticateDashboard::class);
                Route::post('event', SendMessage::class);
            });

            Route::middleware(AuthorizeStatistics::class)->group(function () {
                Route::post('statistics', [WebSocketStatisticsEntriesController::class, 'store']);
            });
        });

        return $this;
    }

    protected function registerDashboardGate()
    {
        Gate::define('viewWebSocketsDashboard', function ($user = null) {
            return app()->environment('local');
        });

        return $this;
    }
}
