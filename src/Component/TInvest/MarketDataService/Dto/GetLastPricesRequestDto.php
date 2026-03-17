<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\MarketDataService\Dto;

final class GetLastPricesRequestDto
{
    /**
     * @param array<string> $instrumentIds
     */
    public function __construct(
        public readonly array $instrumentIds,
    ) {
    }
}
