<?php

declare(strict_types=1);

namespace TInvest\Core\Service\MarketData\Dto;

use DateTimeImmutable;

final readonly class OrderBookViewDto
{
    /**
     * @param array<OrderViewDto> $bids
     * @param array<OrderViewDto> $asks
     */
    public function __construct(
        public readonly string $figi,
        public readonly int $depth,
        public readonly array $bids,
        public readonly array $asks,
        public readonly DateTimeImmutable $time,
        public readonly string $instrumentUid,
        public readonly ?float $limitUp = null,
        public readonly ?float $limitDown = null,
    ) {
    }
}
