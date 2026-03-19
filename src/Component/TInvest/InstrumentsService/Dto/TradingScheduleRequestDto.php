<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Dto;

final readonly class TradingScheduleRequestDto
{
    public function __construct(
        public string $exchange = 'MOEX',
        public ?string $from = null,
        public ?string $to = null,
    ) {
    }
}
