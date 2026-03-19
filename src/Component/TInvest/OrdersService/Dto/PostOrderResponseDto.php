<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OrdersService\Dto;

use TInvest\Core\Component\TInvest\Shared\Dto\MoneyDto;
use TInvest\Core\Component\TInvest\Shared\Dto\QuotationDto;

final readonly class PostOrderResponseDto
{
    public function __construct(
        public readonly ?string $orderId,
        public readonly ?string $executionReportStatus,
        public readonly ?int $lotsRequested,
        public readonly ?int $lotsExecuted,
        public readonly ?MoneyDto $initialOrderPrice,
        public readonly ?MoneyDto $executedOrderPrice,
        public readonly ?MoneyDto $totalOrderAmount,
        public readonly ?MoneyDto $initialCommission,
        public readonly ?MoneyDto $executedCommission,
        public readonly ?MoneyDto $aciValue,
        public readonly ?string $figi,
        public readonly ?string $direction,
        public readonly ?MoneyDto $initialSecurityPrice,
        public readonly ?string $orderType,
        public readonly ?string $message,
        public readonly ?QuotationDto $initialOrderPricePt,
        public readonly ?string $instrumentUid
    ) {
    }
}
