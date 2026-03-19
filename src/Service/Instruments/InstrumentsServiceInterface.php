<?php

declare(strict_types=1);

namespace TInvest\Core\Service\Instruments;

use DateTimeImmutable;
use TInvest\Core\Service\Instruments\Dto\AssetFundamentalViewDto;
use TInvest\Core\Service\Instruments\Dto\AssetReportViewDto;
use TInvest\Core\Service\Instruments\Dto\BondEventViewDto;
use TInvest\Core\Service\Instruments\Dto\DividendViewDto;
use TInvest\Core\Service\Instruments\Dto\TradingScheduleViewDto;

interface InstrumentsServiceInterface
{
    public function getAssetUidByTicker(string $ticker): ?string;

    public function getTickerByAssetUid(string $assetUid): string;

    public function getFigiByTicker(string $ticker): ?string;

    /**
     * @param array<string> $tickers
     * @return array<AssetFundamentalViewDto>
     */
    public function getFundamentalsByTickers(array $tickers): array;

    public function getTradingSchedule(string $exchange, string $from, int $days = 7): TradingScheduleViewDto;

    /**
     * @return array<DividendViewDto>
     */
    public function getDividends(string $ticker, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array;

    /**
     * @return array<AssetReportViewDto>
     */
    public function getAssetReports(string $ticker, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array;

    /**
     * @return array<BondEventViewDto>
     */
    public function getBondEvents(string $ticker, ?string $eventType = null, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array;
}
