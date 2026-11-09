<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\Shared\Dto\MoneyDto;
use TInvest\Core\Component\TInvest\Shared\Dto\QuotationDto;

final readonly class TradeInfoDto
{
    public function __construct(
        public readonly string $num,
        public readonly DateTimeImmutable $date,
        public readonly int $quantity,
        public readonly MoneyDto $price,
        public readonly MoneyDto $yield,
        public readonly QuotationDto $yieldRelative,
    ) {
    }
}
