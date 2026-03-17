<?php

declare(strict_types=1);

namespace TInvest\Skill\Tests\Component\TInvest\MarketDataService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Skill\Component\TInvest\MarketDataService\Mapper\CandleMapper;
use TInvest\Skill\Component\TInvest\Shared\Factory\QuotationFactory;

final class CandleMapperTest extends TestCase
{
    private CandleMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new CandleMapper(new QuotationFactory());
    }

    public function testMapEmptyData(): void
    {
        $result = $this->mapper->map([]);

        $this->assertEmpty($result->candles);
    }

    public function testMapCandles(): void
    {
        $data = [
            'candles' => [
                [
                    'open' => ['units' => '100', 'nano' => 0],
                    'high' => ['units' => '105', 'nano' => 0],
                    'low' => ['units' => '99', 'nano' => 0],
                    'close' => ['units' => '103', 'nano' => 0],
                    'volume' => '1000',
                    'time' => '2024-01-15T10:00:00Z',
                    'isComplete' => true,
                ],
            ],
        ];

        $result = $this->mapper->map($data);

        $this->assertCount(1, $result->candles);
        $candle = $result->candles[0];
        $this->assertSame(100.0, $candle->open->value);
        $this->assertSame(105.0, $candle->high->value);
        $this->assertSame(99.0, $candle->low->value);
        $this->assertSame(103.0, $candle->close->value);
        $this->assertSame(1000, $candle->volume);
        $this->assertTrue($candle->isComplete);
    }

    public function testMapMultipleCandles(): void
    {
        $data = [
            'candles' => [
                [
                    'open' => ['units' => '100', 'nano' => 0],
                    'high' => ['units' => '105', 'nano' => 0],
                    'low' => ['units' => '99', 'nano' => 0],
                    'close' => ['units' => '103', 'nano' => 0],
                    'volume' => '1000',
                    'time' => '2024-01-15T10:00:00Z',
                    'isComplete' => true,
                ],
                [
                    'open' => ['units' => '103', 'nano' => 0],
                    'high' => ['units' => '108', 'nano' => 0],
                    'low' => ['units' => '102', 'nano' => 0],
                    'close' => ['units' => '107', 'nano' => 0],
                    'volume' => '1500',
                    'time' => '2024-01-15T10:01:00Z',
                    'isComplete' => false,
                ],
            ],
        ];

        $result = $this->mapper->map($data);

        $this->assertCount(2, $result->candles);
        $this->assertFalse($result->candles[1]->isComplete);
    }
}
