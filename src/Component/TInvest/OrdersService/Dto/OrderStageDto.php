<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OrdersService\Dto;

use TInvest\Core\Component\TInvest\Shared\Dto\MoneyDto;

final readonly class OrderStageDto
{
    public function __construct(
        public readonly ?MoneyDto $price,
        public readonly int $quantity,
        public readonly string $tradeId
    ) {
    }
}
