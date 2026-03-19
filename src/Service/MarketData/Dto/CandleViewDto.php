<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\MarketData\Dto;

use DateTimeImmutable;

final readonly class CandleViewDto
{
    public function __construct(
        public DateTimeImmutable $time,
        public float $open,
        public float $high,
        public float $low,
        public float $close,
        public int $volume,
        public bool $isComplete,
    ) {
    }
}
