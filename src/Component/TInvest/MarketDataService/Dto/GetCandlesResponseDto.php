<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\MarketDataService\Dto;

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
