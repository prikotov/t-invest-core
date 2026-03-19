<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService\Dto;

final readonly class GetOrderBookRequestDto
{
    public function __construct(
        public readonly string $instrumentId,
        public readonly int $depth = 20,
    ) {
    }
}
