<?php

declare(strict_types=1);

namespace TInvest\Core\Service\MarketData\Dto;

final readonly class OrderViewDto
{
    public function __construct(
        public readonly float $price,
        public readonly int $quantity,
    ) {
    }
}
