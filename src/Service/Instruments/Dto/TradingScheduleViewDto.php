<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Instruments\Dto;

final readonly class TradingScheduleViewDto
{
    /**
     * @param list<TradingDayViewDto> $days
     */
    public function __construct(
        public string $exchange,
        public array $days,
    ) {
    }
}
