<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService\Dto;

final readonly class GetCandlesResponseDto
{
    /**
     * @param array<CandleDto> $candles
     */
    public function __construct(
        public readonly array $candles,
    ) {
    }
}
