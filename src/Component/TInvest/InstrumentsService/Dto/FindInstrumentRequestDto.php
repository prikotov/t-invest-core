<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Dto;

final readonly class FindInstrumentRequestDto
{
    public function __construct(
        public string $query,
    ) {
    }
}
