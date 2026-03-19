<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\OperationsService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\GetPortfolioResponseMapper;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\PercentFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuantityFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

final class GetPortfolioResponseMapperTest extends TestCase
{
    private GetPortfolioResponseMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new GetPortfolioResponseMapper(
            new MoneyFactory(),
            new PercentFactory(),
            new QuantityFactory(),
            new QuotationFactory(),
        );
    }

    public function testMapTotalAmountShares(): void
    {
        $data = [
            'totalAmountShares' => [
                'currency' => 'RUB',
                'units' => '100000',
                'nano' => 500000000,
            ],
            'expectedYield' => ['units' => '0', 'nano' => 0],
        ];

        $result = $this->mapper->map($data);

        $this->assertNotNull($result->totalAmountShares);
        $this->assertSame('RUB', $result->totalAmountShares->currency);
        $this->assertSame(100000.5, $result->totalAmountShares->value);
    }

    public function testMapTotalAmountSharesReturnsNullForEmptyData(): void
    {
        $result = $this->mapper->map([]);

        $this->assertNull($result->totalAmountShares);
    }

    public function testMapExpectedYield(): void
    {
        $data = [
            'expectedYield' => [
                'units' => '15',
                'nano' => 750000000,
            ],
        ];

        $result = $this->mapper->map($data);

        $this->assertSame(15.75, $result->expectedYield->value);
    }

    public function testMapPositions(): void
    {
        $data = [
            'positions' => [
                [
                    'figi' => 'BBG000000001',
                    'instrumentType' => 'share',
                    'quantity' => ['units' => '10', 'nano' => 0],
                    'averagePositionPrice' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
                    'expectedYield' => ['units' => '5', 'nano' => 0],
                    'currentPrice' => ['currency' => 'RUB', 'units' => '105', 'nano' => 0],
                    'averagePositionPriceFifo' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
                    'quantityLots' => ['units' => '1', 'nano' => 0],
                    'blocked' => false,
                    'positionUid' => 'pos-1',
                    'instrumentUid' => 'inst-1',
                    'varMargin' => ['currency' => 'RUB', 'units' => '0', 'nano' => 0],
                    'expectedYieldFifo' => ['units' => '5', 'nano' => 0],
                    'ticker' => 'SBER',
                ],
            ],
            'expectedYield' => ['units' => '0', 'nano' => 0],
        ];

        $result = $this->mapper->map($data);

        $this->assertCount(1, $result->positions);
        $this->assertSame('BBG000000001', $result->positions[0]->figi);
        $this->assertSame('share', $result->positions[0]->instrumentType);
        $this->assertSame(10.0, $result->positions[0]->quantity->value);
        $this->assertFalse($result->positions[0]->blocked);
    }
}
