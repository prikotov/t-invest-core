<?php

declare(strict_types=1);

namespace TInvest\Core\Service\MarketData;

use DateTimeImmutable;
use Generator;
use TInvest\Core\Service\MarketData\Dto\CandleViewDto;
use TInvest\Core\Service\MarketData\Dto\LastPriceViewDto;
use TInvest\Core\Service\MarketData\Dto\OrderBookViewDto;

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

    public function getOrderBook(string $instrumentId, int $depth = 20): OrderBookViewDto;
}
