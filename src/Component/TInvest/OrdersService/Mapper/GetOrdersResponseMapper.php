<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OrdersService\Mapper;

use TInvest\Core\Component\TInvest\OrdersService\Dto\GetOrdersResponseDto;

final class GetOrdersResponseMapper
{
    public function __construct(
        private readonly OrderStateResponseMapper $orderStateResponseMapper,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function map(array $data): GetOrdersResponseDto
    {
        $orders = [];
        /** @var array<int, array<string, mixed>> $orderItems */
        $orderItems = $data['orders'] ?? [];
        foreach ($orderItems as $orderItem) {
            $orders[] = $this->orderStateResponseMapper->map($orderItem);
        }

        return new GetOrdersResponseDto($orders);
    }
}
