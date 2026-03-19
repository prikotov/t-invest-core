<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService\Dto;

use DateTimeImmutable;
use TInvest\Skill\Component\TInvest\Shared\Dto\MoneyDto;
use TInvest\Skill\Component\TInvest\Shared\Dto\QuotationDto;

final readonly class DividendDto
{
    public function __construct(
        public readonly ?MoneyDto $dividendNet,
        public readonly ?DateTimeImmutable $paymentDate,
        public readonly ?DateTimeImmutable $declaredDate,
        public readonly ?DateTimeImmutable $lastBuyDate,
        public readonly string $dividendType,
        public readonly DateTimeImmutable $recordDate,
        public readonly string $regularity,
        public readonly ?MoneyDto $closePrice,
        public readonly ?QuotationDto $yieldValue,
        public readonly DateTimeImmutable $createdAt,
    ) {
    }
}
