<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\Shared\Dto;

final readonly class MoneyDto
{
    public function __construct(
        public readonly string $currency,
        public readonly float $value,
        public readonly int $units,
        public readonly int $nano
    ) {
    }
}
