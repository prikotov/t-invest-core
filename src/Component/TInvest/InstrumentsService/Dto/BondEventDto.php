<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Dto;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\Shared\Dto\MoneyDto;
use TInvest\Core\Component\TInvest\Shared\Dto\QuotationDto;

final readonly class BondEventDto
{
    public function __construct(
        public readonly string $instrumentId,
        public readonly int $eventNumber,
        public readonly ?DateTimeImmutable $eventDate,
        public readonly string $eventType,
        public readonly ?QuotationDto $eventTotalVol,
        public readonly ?DateTimeImmutable $fixDate,
        public readonly ?DateTimeImmutable $payDate,
        public readonly ?MoneyDto $payOneBond,
        public readonly ?int $couponPeriod,
        public readonly ?QuotationDto $couponInterestRate,
        public readonly ?DateTimeImmutable $couponStartDate,
        public readonly ?DateTimeImmutable $couponEndDate,
        public readonly ?string $note,
    ) {
    }
}
