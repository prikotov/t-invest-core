<?php

declare(strict_types=1);

namespace TInvest\Core\Service\Instruments\Dto;

use DateTimeImmutable;

final readonly class DividendViewDto
{
    public function __construct(
        public readonly string $ticker,
        public readonly ?float $dividendNet,
        public readonly ?string $currency,
        public readonly ?DateTimeImmutable $paymentDate,
        public readonly ?DateTimeImmutable $recordDate,
        public readonly ?DateTimeImmutable $lastBuyDate,
        public readonly string $dividendType,
        public readonly ?float $yieldValue,
    ) {
    }
}
