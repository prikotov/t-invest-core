<?php

declare(strict_types=1);

namespace TInvest\Skill\Tests\Component\TInvest\MarketDataService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Skill\Component\TInvest\MarketDataService\Mapper\LastPriceMapper;
use TInvest\Skill\Component\TInvest\Shared\Factory\QuotationFactory;

final class LastPriceMapperTest extends TestCase
{
    private LastPriceMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new LastPriceMapper(new QuotationFactory());
    }

    public function testMapEmptyData(): void
    {
        $result = $this->mapper->map([]);

        $this->assertEmpty($result->lastPrices);
    }

    public function testMapLastPrices(): void
    {
        $data = [
            'lastPrices' => [
                [
                    'figi' => 'BBG000000001',
                    'price' => ['units' => '100', 'nano' => 500000000],
                    'time' => '2024-01-15T10:00:00Z',
                    'ticker' => 'SBER',
                    'classCode' => 'TQBR',
                    'instrumentUid' => 'instrument-1',
                ],
            ],
        ];

        $result = $this->mapper->map($data);

        $this->assertCount(1, $result->lastPrices);
        $lastPrice = $result->lastPrices[0];
        $this->assertSame('BBG000000001', $lastPrice->figi);
        $this->assertSame(100.5, $lastPrice->price->value);
        $this->assertSame('SBER', $lastPrice->ticker);
        $this->assertSame('TQBR', $lastPrice->classCode);
        $this->assertSame('instrument-1', $lastPrice->instrumentUid);
    }

    public function testMapMultipleLastPrices(): void
    {
        $data = [
            'lastPrices' => [
                [
                    'figi' => 'BBG000000001',
                    'price' => ['units' => '100', 'nano' => 0],
                    'instrumentUid' => 'instrument-1',
                ],
                [
                    'figi' => 'BBG000000002',
                    'price' => ['units' => '200', 'nano' => 0],
                    'instrumentUid' => 'instrument-2',
                ],
            ],
        ];

        $result = $this->mapper->map($data);

        $this->assertCount(2, $result->lastPrices);
    }
}
