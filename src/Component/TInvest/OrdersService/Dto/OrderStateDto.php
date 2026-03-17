<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OrdersService\Dto;

use DateTimeImmutable;
use TInvest\Skill\Component\TInvest\Shared\Dto\MoneyDto;

final class OrderStateDto
{
    /**
     * @param array<OrderStageDto> $stages
     */
    public function __construct(
        public readonly string $orderId,
        public readonly string $executionReportStatus,
        public readonly int $lotsRequested,
        public readonly int $lotsExecuted,
        public readonly ?MoneyDto $initialOrderPrice,
        public readonly ?MoneyDto $executedOrderPrice,
        public readonly ?MoneyDto $totalOrderAmount,
        public readonly ?MoneyDto $averagePositionPrice,
        public readonly ?MoneyDto $initialCommission,
        public readonly ?MoneyDto $executedCommission,
        public readonly string $figi,
        public readonly string $direction,
        public readonly ?MoneyDto $initialSecurityPrice,
        public readonly array $stages,
        public readonly ?MoneyDto $serviceCommission,
        public readonly string $currency,
        public readonly string $orderType,
        public readonly DateTimeImmutable $orderDate,
        public readonly string $instrumentUid,
        public readonly string $orderRequestId,
    ) {
    }
}
