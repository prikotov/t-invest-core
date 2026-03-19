<?php

declare(strict_types=1);

namespace TInvest\Core\Service\Operations\Dto;

final readonly class PortfolioViewDto
{
    /**
     * @param list<PortfolioPositionViewDto> $positions
     */
    public function __construct(
        public ?float $totalAmount,
        public ?string $currency,
        public float $expectedYield,
        public array $positions,
    ) {
    }
}
