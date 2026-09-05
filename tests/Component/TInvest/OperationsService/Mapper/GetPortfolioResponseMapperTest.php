<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\OperationsService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\GetPortfolioResponseMapper;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\PercentFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuantityFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;
use UnexpectedValueException;

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
            'positions' => [],
        ];

        $result = $this->mapper->map($data);

        $this->assertNotNull($result->totalAmountShares);
        $this->assertSame('RUB', $result->totalAmountShares->currency);
        $this->assertSame(100000.5, $result->totalAmountShares->value);
    }

    public function testMapTotalAmountSharesReturnsNullForEmptyData(): void
    {
        $result = $this->mapper->map(['positions' => []]);

        $this->assertNull($result->totalAmountShares);
    }

    public function testMapExpectedYield(): void
    {
        $data = [
            'expectedYield' => [
                'units' => '15',
                'nano' => 750000000,
            ],
            'positions' => [],
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

        $positions = iterator_to_array($result->positions, false);
        $this->assertCount(1, $positions);
        $this->assertSame('BBG000000001', $positions[0]->figi);
        $this->assertSame('share', $positions[0]->instrumentType);
        $this->assertSame(10.0, $positions[0]->quantity->value);
        $this->assertFalse($positions[0]->blocked);
    }

    public function testMapMissingPositionsFieldThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('positions" is missing');

        $this->mapper->map(['expectedYield' => ['units' => '0', 'nano' => 0]]);
    }

    public function testMapNullPositionsThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a list');

        $this->mapper->map(['positions' => null]);
    }

    public function testMapPositionsOfWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a list');

        $this->mapper->map(['positions' => 'broken']);
    }

    public function testMapPositionsObjectInsteadOfListThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a list');

        $this->mapper->map(['positions' => ['BBG000000001' => ['figi' => 'BBG000000001']]]);
    }

    public function testMapEmptyPositionsListIsValid(): void
    {
        $result = $this->mapper->map(['positions' => []]);

        $this->assertSame([], iterator_to_array($result->positions, false));
    }

    public function testMapPositionsItemOfWrongTypeThrowsOnIteration(): void
    {
        $result = $this->mapper->map(['positions' => ['broken']]);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('each item');

        $result->positions->rewind();
    }

    /**
     * Построчная семантика: DTO первой позиции доступен до падения
     * маппинга второй — позиции маппятся лениво при итерации.
     */
    public function testMapInvalidSecondPositionDoesNotHideFirstPosition(): void
    {
        $result = $this->mapper->map([
            'positions' => [
                $this->positionData('BBG000000001'),
                $this->positionData('BBG000000002', validQuantity: false),
            ],
        ]);

        $iterator = $result->positions;
        $iterator->rewind();

        $this->assertSame('BBG000000001', $iterator->current()->figi);

        // Падение происходит в момент продвижения итератора: next()
        // выполняет ленивый маппинг второй позиции.
        $this->expectException(\TypeError::class);
        $iterator->next();
    }

    /**
     * @return array<string, mixed>
     */
    private function positionData(string $figi, bool $validQuantity = true): array
    {
        return [
            'figi' => $figi,
            'instrumentType' => 'share',
            'quantity' => $validQuantity ? ['units' => '10', 'nano' => 0] : null,
            'averagePositionPrice' => null,
            'expectedYield' => ['units' => '5', 'nano' => 0],
            'currentPrice' => ['currency' => 'RUB', 'units' => '105', 'nano' => 0],
            'averagePositionPriceFifo' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
            'quantityLots' => ['units' => '1', 'nano' => 0],
            'blocked' => false,
            'positionUid' => 'pos-' . $figi,
            'instrumentUid' => 'inst-' . $figi,
            'varMargin' => ['currency' => 'RUB', 'units' => '0', 'nano' => 0],
            'expectedYieldFifo' => ['units' => '5', 'nano' => 0],
            'ticker' => 'SBER',
        ];
    }
}
