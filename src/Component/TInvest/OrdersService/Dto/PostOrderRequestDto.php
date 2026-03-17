<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OrdersService\Dto;

use TInvest\Skill\Component\TInvest\OrdersService\Enum\OrderDirectionEnum;
use TInvest\Skill\Component\TInvest\OrdersService\Enum\OrderTypeEnum;
use TInvest\Skill\Component\TInvest\Shared\Dto\QuotationDto;

final class PostOrderRequestDto
{
    public function __construct(
        public readonly string $accountId,
        public readonly int $quantity,
        public readonly ?QuotationDto $price,
        public readonly OrderDirectionEnum $direction,
        public readonly OrderTypeEnum $orderType,
        public readonly string $orderId,
        public readonly string $instrumentId,
    ) {
    }
}
