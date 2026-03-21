<?php

declare(strict_types=1);

namespace TInvest\Core\Service\Instruments\Dto;

final readonly class InstrumentSearchDto
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
