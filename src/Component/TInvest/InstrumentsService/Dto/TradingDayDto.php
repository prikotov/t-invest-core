<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Dto;

use DateTimeInterface;

final readonly class TradingDayDto
{
    public function __construct(
        public string $exchange,
        public DateTimeInterface $date,
        public bool $isTradingDay,
        public ?DateTimeInterface $startTime = null,
        public ?DateTimeInterface $endTime = null,
        public ?DateTimeInterface $morningSessionStart = null,
        public ?DateTimeInterface $morningSessionEnd = null,
        public ?DateTimeInterface $eveningSessionStart = null,
        public ?DateTimeInterface $eveningSessionEnd = null,
        public ?DateTimeInterface $clearingStart = null,
        public ?DateTimeInterface $clearingEnd = null,
        public ?string $holidayName = null,
    ) {
    }
}
