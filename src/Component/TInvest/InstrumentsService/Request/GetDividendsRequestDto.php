<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService\Request;

use DateTimeImmutable;

final readonly class GetDividendsRequestDto
{
    public function __construct(
        public readonly string $figi,
        public readonly ?DateTimeImmutable $from,
        public readonly ?DateTimeImmutable $to,
    ) {
    }
}
