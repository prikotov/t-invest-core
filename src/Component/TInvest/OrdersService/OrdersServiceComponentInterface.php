<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OrdersService;

use TInvest\Skill\Component\TInvest\OrdersService\Dto\CancelOrderResponseDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\GetOrdersResponseDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\OrderStateDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\PostOrderRequestDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\PostOrderResponseDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\ReplaceOrderRequestDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\ReplaceOrderResponseDto;

interface OrdersServiceComponentInterface
{
    public function cancelOrder(string $accountId, string $orderId): CancelOrderResponseDto;

    public function getOrderState(string $accountId, string $orderId): OrderStateDto;

    public function getOrders(string $accountId): GetOrdersResponseDto;

    public function postOrder(PostOrderRequestDto $postOrderRequestDto): PostOrderResponseDto;

    public function replaceOrder(ReplaceOrderRequestDto $replaceOrderRequestDto): ReplaceOrderResponseDto;
}
