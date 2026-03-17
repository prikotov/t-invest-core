<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\Shared\Dto;

final class MoneyDto
{
    public function __construct(
        public readonly string $currency,
        public readonly float $value,
        public readonly int $units,
        public readonly int $nano
    ) {
    }
}
