<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\MarketDataService\Dto;

use DateTimeImmutable;
use TInvest\Skill\Component\TInvest\Shared\Dto\QuotationDto;

final class LastPriceDto
{
    public function __construct(
        public readonly string $figi,
        public readonly QuotationDto $price,
        public readonly ?DateTimeImmutable $time,
        public readonly ?string $ticker,
        public readonly ?string $classCode,
        public readonly string $instrumentUid,
    ) {
    }
}
