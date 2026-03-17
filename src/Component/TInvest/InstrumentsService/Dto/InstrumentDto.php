<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService\Dto;

use DateTimeImmutable;
use TInvest\Skill\Component\TInvest\Shared\Dto\QuantityDto;

final class InstrumentDto
{
    public function __construct(
        public readonly string $figi,
        public readonly string $ticker,
        public readonly string $classCode,
        public readonly ?string $isin,
        public readonly int $lot,
        public readonly string $currency,
        public readonly QuantityDto $klong,
        public readonly QuantityDto $kshort,
        public readonly QuantityDto $dlong,
        public readonly QuantityDto $dshort,
        public readonly QuantityDto $dlongMin,
        public readonly QuantityDto $dshortMin,
        public readonly bool $shortEnabledFlag,
        public readonly string $name,
        public readonly string $exchange,
        public readonly ?string $countryOfRisk,
        public readonly ?string $countryOfRiskName,
        public readonly string $instrumentType,
        public readonly int $tradingStatus,
        public readonly ?bool $otcFlag,
        public readonly bool $buyAvailableFlag,
        public readonly bool $sellAvailableFlag,
        public readonly QuantityDto $minPriceIncrement,
        public readonly ?bool $apiTradeAvailableFlag,
        public readonly string $uid,
        public readonly string $realExchange,
        public readonly string $positionUid,
        public readonly bool $forIisFlag,
        public readonly bool $forQualInvestorFlag,
        public readonly bool $weekendFlag,
        public readonly bool $blockedTcaFlag,
        public readonly string $instrumentKind,
        public readonly ?DateTimeImmutable $first1minCandleDate,
        public readonly ?DateTimeImmutable $first1dayCandleDate,
        public readonly string $assetUid,
    ) {
    }
}
