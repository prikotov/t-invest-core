<?php

declare(strict_types=1);

namespace TInvest\Skill\Tests\Component\TInvest\OrdersService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\OrderStageDto;
use TInvest\Skill\Component\TInvest\OrdersService\Mapper\OrderStateResponseMapper;
use TInvest\Skill\Component\TInvest\Shared\Factory\MoneyFactory;

final class OrderStateResponseMapperTest extends TestCase
{
    public function testMapReturnsOrderStateDto(): void
    {
        $data = [
            'orderId' => 'order-123',
            'executionReportStatus' => 'EXECUTION_REPORT_STATUS_FILL',
            'lotsRequested' => '10',
            'lotsExecuted' => '10',
            'initialOrderPrice' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 0],
            'executedOrderPrice' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 0],
            'totalOrderAmount' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 0],
            'averagePositionPrice' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
            'initialCommission' => ['currency' => 'RUB', 'units' => '1', 'nano' => 0],
            'executedCommission' => ['currency' => 'RUB', 'units' => '1', 'nano' => 0],
            'figi' => 'BBG000000001',
            'direction' => 'ORDER_DIRECTION_BUY',
            'initialSecurityPrice' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
            'stages' => [
                [
                    'price' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
                    'quantity' => '10',
                    'tradeId' => 'trade-1',
                ],
            ],
            'serviceCommission' => ['currency' => 'RUB', 'units' => '0', 'nano' => 50000000],
            'currency' => 'RUB',
            'orderType' => 'ORDER_TYPE_LIMIT',
            'orderDate' => '2024-01-15T10:30:00Z',
            'instrumentUid' => 'inst-1',
            'orderRequestId' => 'req-1',
        ];

        $mapper = new OrderStateResponseMapper(new MoneyFactory());
        $result = $mapper->map($data);

        $this->assertSame('order-123', $result->orderId);
        $this->assertSame('EXECUTION_REPORT_STATUS_FILL', $result->executionReportStatus);
        $this->assertSame(10, $result->lotsRequested);
        $this->assertSame(10, $result->lotsExecuted);
        $this->assertSame('BBG000000001', $result->figi);
        $this->assertSame('ORDER_DIRECTION_BUY', $result->direction);
        $this->assertSame('ORDER_TYPE_LIMIT', $result->orderType);
        $this->assertSame('RUB', $result->currency);
        $this->assertCount(1, $result->stages);
        $this->assertInstanceOf(OrderStageDto::class, $result->stages[0]);
        $this->assertSame('trade-1', $result->stages[0]->tradeId);
        $this->assertSame(10, $result->stages[0]->quantity);
    }
}
