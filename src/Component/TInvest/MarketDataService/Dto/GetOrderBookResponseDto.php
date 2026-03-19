<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService\Dto;

use DateTimeImmutable;

final readonly class GetOrderBookResponseDto
{
    /**
     * @param array<OrderDto> $bids
     * @param array<OrderDto> $asks
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
