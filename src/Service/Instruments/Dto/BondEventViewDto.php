<?php

declare(strict_types=1);

namespace TInvest\Core\Service\Instruments\Dto;

use DateTimeImmutable;

final readonly class BondEventViewDto
{
    public function __construct(
        public readonly string $ticker,
        public readonly int $eventNumber,
        public readonly ?DateTimeImmutable $eventDate,
        public readonly string $eventType,
        public readonly ?float $payOneBond,
        public readonly ?string $currency,
        public readonly ?int $couponPeriod,
        public readonly ?float $couponInterestRate,
        public readonly ?string $note,
    ) {
    }
}
