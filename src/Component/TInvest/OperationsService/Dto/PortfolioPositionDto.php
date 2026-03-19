<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

use TInvest\Core\Component\TInvest\Shared\Dto\MoneyDto;
use TInvest\Core\Component\TInvest\Shared\Dto\QuantityDto;
use TInvest\Core\Component\TInvest\Shared\Dto\QuotationDto;

final readonly class PortfolioPositionDto
{
    public function __construct(
        public readonly string $figi,
        public readonly string $instrumentType,
        public readonly QuantityDto $quantity,
        public readonly ?MoneyDto $averagePositionPrice,
        public readonly QuotationDto $expectedYield,
        public readonly ?MoneyDto $currentNkd,
        public readonly MoneyDto $currentPrice,
        public readonly ?MoneyDto $averagePositionPricePt,
        public readonly MoneyDto $averagePositionPriceFifo,
        public readonly QuantityDto $quantityLots,
        public readonly bool $blocked,
        public readonly ?QuotationDto $blockedLots,
        public readonly string $positionUid,
        public readonly string $instrumentUid,
        public readonly MoneyDto $varMargin,
        public readonly QuotationDto $expectedYieldFifo,
        public readonly string $ticker,
    ) {
    }
}
