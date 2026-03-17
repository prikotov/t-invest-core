<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService\Dto;

final readonly class InstrumentShortDto
{
    public function __construct(
        public string $ticker,
        public string $uid,
        public string $instrumentType,
    ) {
    }
}
