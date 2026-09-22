<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\MarketDataService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Core\Component\TInvest\MarketDataService\Mapper\OrderBookMapper;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

final class OrderBookMapperTest extends TestCase
{
    private OrderBookMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new OrderBookMapper(new QuotationFactory());
    }

    public function testMapEmptyData(): void
    {
        $result = $this->mapper->map([]);

        $this->assertSame('', $result->figi);
        $this->assertSame([], $result->bids);
        $this->assertSame([], $result->asks);
        $this->assertNull($result->time);
        $this->assertNull($result->limitUp);
        $this->assertNull($result->limitDown);
        $this->assertNull($result->lastPrice);
        $this->assertNull($result->lastPriceTs);
    }

    public function testMapOrderBook(): void
    {
        $data = [
            'figi' => 'BBG000000001',
            'depth' => 2,
            'bids' => [
                ['price' => ['units' => '100', 'nano' => 100000000], 'quantity' => 10],
            ],
            'asks' => [
                ['price' => ['units' => '101', 'nano' => 900000000], 'quantity' => 20],
            ],
            'time' => '2024-01-15T10:00:00Z',
            'instrumentUid' => 'instrument-1',
            'limitUp' => ['units' => '110', 'nano' => 0],
            'limitDown' => ['units' => '90', 'nano' => 0],
            'lastPrice' => ['units' => '101', 'nano' => 500000000],
            'lastPriceTs' => '2024-01-15T09:59:59Z',
        ];

        $result = $this->mapper->map($data);

        $this->assertSame('BBG000000001', $result->figi);
        $this->assertSame(2, $result->depth);
        $this->assertSame('instrument-1', $result->instrumentUid);

        $this->assertCount(1, $result->bids);
        $this->assertSame(100.1, $result->bids[0]->price);
        $this->assertSame(10, $result->bids[0]->quantity);

        $this->assertCount(1, $result->asks);
        $this->assertSame(101.9, $result->asks[0]->price);
        $this->assertSame(20, $result->asks[0]->quantity);

        $this->assertSame(110.0, $result->limitUp);
        $this->assertSame(90.0, $result->limitDown);

        $this->assertSame(101.5, $result->lastPrice);
        $this->assertSame('2024-01-15T10:00:00+00:00', $result->time?->format(DATE_ATOM));
        $this->assertSame('2024-01-15T09:59:59+00:00', $result->lastPriceTs?->format(DATE_ATOM));
    }

    public function testMapMissingTimeDoesNotFallBackToCurrentTime(): void
    {
        $data = [
            'figi' => 'BBG000000001',
            'bids' => [],
            'asks' => [],
        ];

        $result = $this->mapper->map($data);

        $this->assertNull($result->time);
    }

    public function testMapMissingLastPriceFields(): void
    {
        $data = [
            'figi' => 'BBG000000001',
            'bids' => [],
            'asks' => [],
            'time' => '2024-01-15T10:00:00Z',
        ];

        $result = $this->mapper->map($data);

        $this->assertNull($result->lastPrice);
        $this->assertNull($result->lastPriceTs);
        $this->assertNotNull($result->time);
    }
}
