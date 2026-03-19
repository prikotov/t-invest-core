<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Dto;

final readonly class TradingScheduleDto
{
    /**
     * @param TradingDayDto[] $days
     */
    public function __construct(
        public string $exchange,
        public array $days,
    ) {
    }
}
