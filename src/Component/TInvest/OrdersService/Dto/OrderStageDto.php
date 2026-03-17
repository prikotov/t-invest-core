<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OrdersService\Dto;

use TInvest\Skill\Component\TInvest\Shared\Dto\MoneyDto;

final class OrderStageDto
{
    public function __construct(
        public readonly ?MoneyDto $price,
        public readonly int $quantity,
        public readonly string $tradeId
    ) {
    }
}
