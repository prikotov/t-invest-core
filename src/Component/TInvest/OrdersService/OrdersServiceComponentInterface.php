<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OrdersService;

use TInvest\Core\Component\TInvest\OrdersService\Dto\CancelOrderResponseDto;
use TInvest\Core\Component\TInvest\OrdersService\Dto\GetOrdersResponseDto;
use TInvest\Core\Component\TInvest\OrdersService\Dto\OrderStateDto;
use TInvest\Core\Component\TInvest\OrdersService\Dto\PostOrderRequestDto;
use TInvest\Core\Component\TInvest\OrdersService\Dto\PostOrderResponseDto;
use TInvest\Core\Component\TInvest\OrdersService\Dto\ReplaceOrderRequestDto;
use TInvest\Core\Component\TInvest\OrdersService\Dto\ReplaceOrderResponseDto;

interface OrdersServiceComponentInterface
{
    public function cancelOrder(string $accountId, string $orderId): CancelOrderResponseDto;

    public function getOrderState(string $accountId, string $orderId): OrderStateDto;

    public function getOrders(string $accountId): GetOrdersResponseDto;

    public function postOrder(PostOrderRequestDto $postOrderRequestDto): PostOrderResponseDto;

    public function replaceOrder(ReplaceOrderRequestDto $replaceOrderRequestDto): ReplaceOrderResponseDto;
}
