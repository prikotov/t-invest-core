<?php

declare(strict_types=1);

namespace TInvest\Skill\Tests\Component\TInvest\OrdersService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\PostOrderRequestDto;
use TInvest\Skill\Component\TInvest\OrdersService\Enum\OrderDirectionEnum;
use TInvest\Skill\Component\TInvest\OrdersService\Enum\OrderTypeEnum;
use TInvest\Skill\Component\TInvest\OrdersService\Mapper\PostOrderRequestMapper;
use TInvest\Skill\Component\TInvest\Shared\Dto\QuotationDto;

final class PostOrderRequestMapperTest extends TestCase
{
    public function testMapWithPrice(): void
    {
        $dto = new PostOrderRequestDto(
            accountId: 'account-1',
            quantity: 10,
            price: new QuotationDto(100.5, 100, 500000000),
            direction: OrderDirectionEnum::ORDER_DIRECTION_BUY,
            orderType: OrderTypeEnum::ORDER_TYPE_LIMIT,
            orderId: 'order-123',
            instrumentId: 'BBG000000001',
        );

        $mapper = new PostOrderRequestMapper();
        $result = $mapper->map($dto);
        $data = json_decode($result, true);

        $this->assertSame(10, $data['quantity']);
        $this->assertSame('ORDER_DIRECTION_BUY', $data['direction']);
        $this->assertSame('account-1', $data['accountId']);
        $this->assertSame('ORDER_TYPE_LIMIT', $data['orderType']);
        $this->assertSame('order-123', $data['orderId']);
        $this->assertSame('BBG000000001', $data['instrumentId']);
        $this->assertArrayHasKey('price', $data);
        $this->assertSame(100, $data['price']['units']);
        $this->assertSame(500000000, $data['price']['nano']);
    }

    public function testMapWithoutPrice(): void
    {
        $dto = new PostOrderRequestDto(
            accountId: 'account-1',
            quantity: 5,
            price: null,
            direction: OrderDirectionEnum::ORDER_DIRECTION_SELL,
            orderType: OrderTypeEnum::ORDER_TYPE_MARKET,
            orderId: 'order-456',
            instrumentId: 'BBG000000002',
        );

        $mapper = new PostOrderRequestMapper();
        $result = $mapper->map($dto);
        $data = json_decode($result, true);

        $this->assertSame(5, $data['quantity']);
        $this->assertSame('ORDER_DIRECTION_SELL', $data['direction']);
        $this->assertSame('ORDER_TYPE_MARKET', $data['orderType']);
        $this->assertArrayNotHasKey('price', $data);
    }
}
