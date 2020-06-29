<?php

namespace Kplaricos\LaravelWebSockets\Tests\Statistics\Logger;

use Kplaricos\LaravelWebSockets\Statistics\Logger\HttpStatisticsLogger;

class FakeStatisticsLogger extends HttpStatisticsLogger
{
    public function save()
    {
        foreach ($this->statistics as $appId => $statistic) {
            $currentConnectionCount = $this->channelManager->getConnectionCount($appId);
            $statistic->reset($currentConnectionCount);
        }
    }

    public function getForAppId($appId): array
    {
        $statistic = $this->findOrMakeStatisticForAppId($appId);

        return $statistic->toArray();
    }
}
