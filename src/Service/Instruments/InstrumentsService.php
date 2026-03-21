<?php

declare(strict_types=1);

namespace TInvest\Core\Service\Instruments;

use DateTimeImmutable;
use Override;
use Symfony\Contracts\Cache\CacheInterface;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\FindInstrumentRequestDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\GetAssetFundamentalsRequestDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\GetAssetReportsRequestDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\GetBondEventsRequestDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\TradingScheduleRequestDto;
use TInvest\Core\Component\TInvest\InstrumentsService\InstrumentsServiceComponentInterface;
use TInvest\Core\Component\TInvest\InstrumentsService\Request\GetDividendsRequestDto;
use TInvest\Core\Service\Instruments\Dto\AssetFundamentalViewDto;
use TInvest\Core\Service\Instruments\Dto\AssetReportViewDto;
use TInvest\Core\Service\Instruments\Dto\BondEventViewDto;
use TInvest\Core\Service\Instruments\Dto\DividendViewDto;
use TInvest\Core\Service\Instruments\Dto\InstrumentSearchDto;
use TInvest\Core\Service\Instruments\Dto\TradingDayViewDto;
use TInvest\Core\Service\Instruments\Dto\TradingScheduleViewDto;

final class InstrumentsService implements InstrumentsServiceInterface
{
    private const CACHE_PREFIX = 'tinvest_instrument_';

    public function __construct(
        private readonly InstrumentsServiceComponentInterface $component,
        private readonly CacheInterface $cache,
    ) {
    }

    #[Override]
    public function search(string $query): array
    {
        $findResponse = $this->component->findInstrument(new FindInstrumentRequestDto($query));

        $result = [];
        foreach ($findResponse->instruments as $instrument) {
            $result[] = new InstrumentSearchDto(
                ticker: $instrument->ticker,
                uid: $instrument->uid,
                instrumentType: $instrument->instrumentType,
                classCode: $instrument->classCode,
                apiTradeAvailableFlag: $instrument->apiTradeAvailableFlag,
            );
        }

        return $result;
    }

    #[Override]
    public function getInstrumentUidByTicker(string $ticker): ?string
    {
        return $this->findBestInstrumentUid($ticker);
    }

    #[Override]
    public function getAssetUidByTicker(string $ticker): ?string
    {
        $uid = $this->findBestInstrumentUid($ticker);

        if ($uid === null) {
            return null;
        }

        $instrument = $this->component->getInstrumentBy($uid, 'INSTRUMENT_ID_TYPE_UID');
        return $instrument->assetUid;
    }

    #[Override]
    public function getTickerByAssetUid(string $assetUid): string
    {
        $instrument = $this->component->getInstrumentBy($assetUid, 'INSTRUMENT_ID_TYPE_UID');
        return $instrument->ticker;
    }

    #[Override]
    public function getFigiByTicker(string $ticker): ?string
    {
        $uid = $this->findBestInstrumentUid($ticker);

        if ($uid === null) {
            return null;
        }

        $instrument = $this->component->getInstrumentBy($uid, 'INSTRUMENT_ID_TYPE_UID');
        return $instrument->figi;
    }

    #[Override]
    public function getTickerByFigi(string $figi): ?string
    {
        $cacheKey = self::CACHE_PREFIX . 'figi_' . strtolower($figi);

        return $this->cache->get($cacheKey, function () use ($figi): string {
            $instrument = $this->component->getInstrumentBy($figi, 'INSTRUMENT_ID_TYPE_FIGI');
            return $instrument->ticker;
        });
    }

    private function findBestInstrumentUid(string $ticker): ?string
    {
        $findResponse = $this->component->findInstrument(new FindInstrumentRequestDto($ticker));

        $fallback = null;
        foreach ($findResponse->instruments as $instrument) {
            if ($instrument->ticker !== $ticker) {
                continue;
            }

            if ($instrument->classCode === 'TQBR') {
                return $instrument->uid;
            }

            if ($instrument->apiTradeAvailableFlag && $fallback === null) {
                $fallback = $instrument->uid;
            }
        }

        if ($fallback !== null) {
            return $fallback;
        }

        if ($findResponse->instruments !== []) {
            return $findResponse->instruments[0]->uid;
        }

        return null;
    }

    #[Override]
    public function getFundamentalsByTickers(array $tickers): array
    {
        $assetUids = [];
        $tickerToUid = [];

        foreach ($tickers as $ticker) {
            $uid = $this->getAssetUidByTicker($ticker);
            if ($uid !== null) {
                $assetUids[] = $uid;
                $tickerToUid[$uid] = $ticker;
            }
        }

        if ($assetUids === []) {
            return [];
        }

        $response = $this->component->getAssetFundamentals(new GetAssetFundamentalsRequestDto($assetUids));

        $result = [];
        foreach ($response->fundamentals as $fundamental) {
            $ticker = $tickerToUid[$fundamental->assetUid] ?? $fundamental->assetUid;
            $result[] = new AssetFundamentalViewDto(
                ticker: $ticker,
                marketCapitalization: $fundamental->marketCapitalization,
                peRatioTtm: $fundamental->peRatioTtm,
                priceToBookTtm: $fundamental->priceToBookTtm,
                priceToSalesTtm: $fundamental->priceToSalesTtm,
                roe: $fundamental->roe,
                roa: $fundamental->roa,
                dividendYieldDailyTtm: $fundamental->dividendYieldDailyTtm,
                epsTtm: $fundamental->epsTtm,
                revenueTtm: $fundamental->revenueTtm,
                netIncomeTtm: $fundamental->netIncomeTtm,
                ebitdaTtm: $fundamental->ebitdaTtm,
                freeCashFlowTtm: $fundamental->freeCashFlowTtm,
                beta: $fundamental->beta,
                highPriceLast52Weeks: $fundamental->highPriceLast52Weeks,
                lowPriceLast52Weeks: $fundamental->lowPriceLast52Weeks,
                currency: $fundamental->currency,
            );
        }

        return $result;
    }

    #[Override]
    public function getTradingSchedule(string $exchange, string $from, int $days = 7): TradingScheduleViewDto
    {
        $timestamp = strtotime($from . ' +' . $days . ' days');
        $to = $timestamp !== false ? date('Y-m-d', $timestamp) : null;

        $request = new TradingScheduleRequestDto(
            exchange: $exchange,
            from: $from,
            to: $to,
        );

        $schedule = $this->component->getTradingSchedule($request);

        $days = array_map(
            fn($day) => new TradingDayViewDto(
                date: $day->date,
                isTradingDay: $day->isTradingDay,
                startTime: $day->startTime,
                endTime: $day->endTime,
                morningSessionStart: $day->morningSessionStart,
                morningSessionEnd: $day->morningSessionEnd,
                eveningSessionStart: $day->eveningSessionStart,
                eveningSessionEnd: $day->eveningSessionEnd,
                clearingStart: $day->clearingStart,
                clearingEnd: $day->clearingEnd,
                holidayName: $day->holidayName,
            ),
            $schedule->days
        );

        return new TradingScheduleViewDto(
            exchange: $schedule->exchange,
            days: array_values($days),
        );
    }

    #[Override]
    public function getDividends(string $ticker, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        $figi = $this->getFigiByTicker($ticker);
        if ($figi === null) {
            return [];
        }

        $request = new GetDividendsRequestDto($figi, $from, $to);
        $dividends = iterator_to_array($this->component->getDividends($request));

        $result = [];
        foreach ($dividends as $dividend) {
            $result[] = new DividendViewDto(
                ticker: $ticker,
                dividendNet: $dividend->dividendNet?->value,
                currency: $dividend->dividendNet?->currency,
                paymentDate: $dividend->paymentDate,
                recordDate: $dividend->recordDate,
                lastBuyDate: $dividend->lastBuyDate,
                dividendType: $dividend->dividendType,
                yieldValue: $dividend->yieldValue?->value,
            );
        }

        usort($result, fn(DividendViewDto $a, DividendViewDto $b): int => 
            ($b->recordDate ?? $b->paymentDate ?? new DateTimeImmutable('@0')) 
            <=> 
            ($a->recordDate ?? $a->paymentDate ?? new DateTimeImmutable('@0'))
        );

        return $result;
    }

    #[Override]
    public function getAssetReports(string $ticker, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        $assetUid = $this->getAssetUidByTicker($ticker);
        if ($assetUid === null) {
            return [];
        }

        $request = new GetAssetReportsRequestDto($assetUid, $from, $to);
        $reports = iterator_to_array($this->component->getAssetReports($request));

        $result = [];
        foreach ($reports as $report) {
            $result[] = new AssetReportViewDto(
                ticker: $ticker,
                reportDate: $report->reportDate,
                periodYear: $report->periodYear,
                periodNum: $report->periodNum,
                periodType: $report->periodType,
            );
        }

        usort($result, fn(AssetReportViewDto $a, AssetReportViewDto $b): int => 
            ($b->reportDate ?? new DateTimeImmutable('@0')) <=> ($a->reportDate ?? new DateTimeImmutable('@0'))
        );

        return $result;
    }

    #[Override]
    public function getBondEvents(string $ticker, ?string $eventType = null, ?DateTimeImmutable $from = null, ?DateTimeImmutable $to = null): array
    {
        $figi = $this->getFigiByTicker($ticker);
        if ($figi === null) {
            return [];
        }

        $request = new GetBondEventsRequestDto($figi, $eventType, $from, $to);
        $events = iterator_to_array($this->component->getBondEvents($request));

        $result = [];
        foreach ($events as $event) {
            $result[] = new BondEventViewDto(
                ticker: $ticker,
                eventNumber: $event->eventNumber,
                eventDate: $event->eventDate,
                eventType: $event->eventType,
                payOneBond: $event->payOneBond?->value,
                currency: $event->payOneBond?->currency,
                couponPeriod: $event->couponPeriod,
                couponInterestRate: $event->couponInterestRate?->value,
                note: $event->note,
            );
        }

        usort($result, fn(BondEventViewDto $a, BondEventViewDto $b): int => 
            ($b->eventDate ?? new DateTimeImmutable('@0')) <=> ($a->eventDate ?? new DateTimeImmutable('@0'))
        );

        return $result;
    }
}
