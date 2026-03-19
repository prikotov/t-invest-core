<?php

declare(strict_types=1);

namespace TInvest\Core\Service\MarketData;

use DateTimeImmutable;
use Generator;
use Override;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetCandlesRequestDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetLastPricesRequestDto;
use TInvest\Core\Component\TInvest\MarketDataService\Enum\CandleIntervalEnum;
use TInvest\Core\Component\TInvest\MarketDataService\MarketDataServiceComponentInterface;
use TInvest\Core\Service\MarketData\Dto\CandleViewDto;
use TInvest\Core\Service\MarketData\Dto\LastPriceViewDto;

final class MarketDataService implements MarketDataServiceInterface
{
    public function __construct(
        private readonly MarketDataServiceComponentInterface $component,
    ) {
    }

    #[Override]
    public function getCandles(
        string $instrumentId,
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        string $interval = '1h',
        int $limit = 100
    ): Generator {
        $intervalEnum = $this->parseInterval($interval);
        $request = new GetCandlesRequestDto($instrumentId, $from, $to, $intervalEnum, $limit);
        $response = $this->component->getCandles($request);

        foreach ($response->candles as $candle) {
            yield new CandleViewDto(
                time: $candle->time,
                open: $candle->open->value,
                high: $candle->high->value,
                low: $candle->low->value,
                close: $candle->close->value,
                volume: $candle->volume,
                isComplete: $candle->isComplete,
            );
        }
    }

    #[Override]
    public function getLastPrices(array $instrumentIds): Generator
    {
        $request = new GetLastPricesRequestDto($instrumentIds);
        $response = $this->component->getLastPrices($request);

        foreach ($response->lastPrices as $lastPrice) {
            yield new LastPriceViewDto(
                figi: $lastPrice->figi,
                price: $lastPrice->price->value,
                time: $lastPrice->time,
                ticker: $lastPrice->ticker,
            );
        }
    }

    private function parseInterval(string $interval): CandleIntervalEnum
    {
        return match ($interval) {
            '5s' => CandleIntervalEnum::FIVE_SEC,
            '10s' => CandleIntervalEnum::TEN_SEC,
            '30s' => CandleIntervalEnum::THIRTY_SEC,
            '1m', '1min' => CandleIntervalEnum::ONE_MIN,
            '2m', '2min' => CandleIntervalEnum::TWO_MIN,
            '3m', '3min' => CandleIntervalEnum::THREE_MIN,
            '5m', '5min' => CandleIntervalEnum::FIVE_MIN,
            '10m', '10min' => CandleIntervalEnum::TEN_MIN,
            '15m', '15min' => CandleIntervalEnum::FIFTEEN_MIN,
            '30m', '30min' => CandleIntervalEnum::THIRTY_MIN,
            '1h', '1hour' => CandleIntervalEnum::HOUR,
            '2h', '2hour' => CandleIntervalEnum::TWO_HOUR,
            '4h', '4hour' => CandleIntervalEnum::FOUR_HOUR,
            '1d', 'day' => CandleIntervalEnum::DAY,
            '1w', 'week' => CandleIntervalEnum::WEEK,
            '1M', 'month' => CandleIntervalEnum::MONTH,
            default => CandleIntervalEnum::HOUR,
        };
    }
}
