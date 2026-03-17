<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Portfolio\Dto;

final class PortfolioPositionViewDto
{
    public function __construct(
        public readonly string $figi,
        public readonly string $instrumentType,
        public readonly float $quantity,
        public readonly string $price,
        public readonly float $yield,
    ) {
    }
}
