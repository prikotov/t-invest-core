<?php

declare(strict_types=1);

namespace TInvest\Core\Service\Operations\Dto;

final readonly class PortfolioPositionViewDto
{
    public function __construct(
        public string $ticker,
        public string $instrumentType,
        public float $quantity,
        public float $avgPrice,
        public float $currentPrice,
        public float $expectedYield,
    ) {
    }
}
