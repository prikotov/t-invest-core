<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\OrdersService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Core\Component\TInvest\OrdersService\Mapper\PostOrderResponseMapper;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

final class PostOrderResponseMapperTest extends TestCase
{
    public function testMapReturnsPostOrderResponseDto(): void
    {
        $data = [
            'orderId' => 'order-123',
            'executionReportStatus' => 'EXECUTION_REPORT_STATUS_FILL',
            'lotsRequested' => '10',
            'lotsExecuted' => '10',
            'initialOrderPrice' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 0],
            'executedOrderPrice' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 0],
            'totalOrderAmount' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 0],
            'initialCommission' => ['currency' => 'RUB', 'units' => '1', 'nano' => 0],
            'executedCommission' => ['currency' => 'RUB', 'units' => '1', 'nano' => 0],
            'figi' => 'BBG000000001',
            'direction' => 'ORDER_DIRECTION_BUY',
            'initialSecurityPrice' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
            'orderType' => 'ORDER_TYPE_LIMIT',
            'message' => '',
            'instrumentUid' => 'inst-1',
        ];

        $mapper = new PostOrderResponseMapper(new MoneyFactory(), new QuotationFactory());
        $result = $mapper->map($data);

        $this->assertSame('order-123', $result->orderId);
        $this->assertSame('EXECUTION_REPORT_STATUS_FILL', $result->executionReportStatus);
        $this->assertSame(10, $result->lotsRequested);
        $this->assertSame(10, $result->lotsExecuted);
        $this->assertSame('BBG000000001', $result->figi);
        $this->assertSame('ORDER_DIRECTION_BUY', $result->direction);
        $this->assertSame('ORDER_TYPE_LIMIT', $result->orderType);
        $this->assertNotNull($result->initialOrderPrice);
        $this->assertSame('RUB', $result->initialOrderPrice->currency);
    }
}
