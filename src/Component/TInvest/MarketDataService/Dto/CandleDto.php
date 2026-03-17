<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\MarketDataService\Dto;

use DateTimeImmutable;
use TInvest\Skill\Component\TInvest\Shared\Dto\QuotationDto;

final class CandleDto
{
    public function __construct(
        public readonly QuotationDto $open,
        public readonly QuotationDto $high,
        public readonly QuotationDto $low,
        public readonly QuotationDto $close,
        public readonly int $volume,
        public readonly DateTimeImmutable $time,
        public readonly bool $isComplete,
        public readonly ?int $volumeBuy,
        public readonly ?int $volumeSell,
    ) {
    }
}
