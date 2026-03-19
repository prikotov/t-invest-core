<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\MarketData;

use DateTimeImmutable;
use Generator;
use TInvest\Skill\Service\MarketData\Dto\CandleViewDto;
use TInvest\Skill\Service\MarketData\Dto\LastPriceViewDto;

interface MarketDataServiceInterface
{
    public function getCandles(
        string $instrumentId,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        string $interval = '1h',
        int $limit = 100
    ): Generator;

    /**
     * @param array<string> $instrumentIds
     * @return Generator<LastPriceViewDto>
     */
    public function getLastPrices(array $instrumentIds): Generator;
}
