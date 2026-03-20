<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Dto;

final readonly class InstrumentShortDto
{
    public function __construct(
        public string $ticker,
        public string $uid,
        public string $instrumentType,
        public string $classCode,
        public bool $apiTradeAvailableFlag,
    ) {
    }
}
