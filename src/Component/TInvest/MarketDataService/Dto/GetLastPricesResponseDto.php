<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\MarketDataService\Dto;

final readonly class GetLastPricesResponseDto
{
    /**
     * @param array<LastPriceDto> $lastPrices
     */
    public function __construct(
        public readonly array $lastPrices,
    ) {
    }
}
