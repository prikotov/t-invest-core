<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService\Dto;

final readonly class FindInstrumentRequestDto
{
    public function __construct(
        public string $query,
    ) {
    }
}
