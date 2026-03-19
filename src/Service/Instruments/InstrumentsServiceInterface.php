<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Instruments;

use TInvest\Skill\Service\Instruments\Dto\AssetFundamentalViewDto;
use TInvest\Skill\Service\Instruments\Dto\TradingScheduleViewDto;

interface InstrumentsServiceInterface
{
    public function getAssetUidByTicker(string $ticker): ?string;

    public function getTickerByAssetUid(string $assetUid): string;

    public function getFundamentalsByTickers(array $tickers): array;

    public function getTradingSchedule(string $exchange, string $from, int $days = 7): TradingScheduleViewDto;
}
