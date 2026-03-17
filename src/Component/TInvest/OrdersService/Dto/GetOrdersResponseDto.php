<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OrdersService\Dto;

final class GetOrdersResponseDto
{
    /**
     * @param array<OrderStateDto> $orders
     */
    public function __construct(
        public readonly array $orders
    ) {
    }
}
