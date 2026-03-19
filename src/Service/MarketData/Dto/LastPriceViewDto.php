<?php

declare(strict_types=1);

namespace TInvest\Core\Service\MarketData\Dto;

use DateTimeImmutable;

final readonly class LastPriceViewDto
{
    public function __construct(
        public string $figi,
        public float $price,
        public ?DateTimeImmutable $time,
        public ?string $ticker,
    ) {
    }
}
