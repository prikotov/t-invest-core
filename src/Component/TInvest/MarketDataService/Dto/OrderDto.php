<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService\Dto;

final readonly class OrderDto
{
    public function __construct(
        public readonly float $price,
        public readonly int $quantity,
    ) {
    }
}
