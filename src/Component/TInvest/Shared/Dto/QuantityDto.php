<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\Shared\Dto;

final readonly class QuantityDto
{
    public function __construct(
        public readonly float $value,
        public readonly int $units,
        public readonly int $nano
    ) {
    }
}
