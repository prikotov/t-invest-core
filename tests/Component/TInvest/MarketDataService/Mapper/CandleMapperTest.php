<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\MarketDataService\Mapper;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use TInvest\Core\Component\TInvest\MarketDataService\Mapper\CandleMapper;
use TInvest\Core\Component\TInvest\Shared\Exception\TInvestResponseException;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

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

    public function testMapValidObjectWithoutCandlesKeyReturnsEmptyResponse(): void
    {
        $result = $this->mapper->map(['unrelated' => true]);

        $this->assertSame([], $result->candles);
    }

    public function testMapCandlesFieldOfWrongTypeThrows(): void
    {
        $this->expectException(TInvestResponseException::class);
        $this->expectExceptionMessage('field "candles" must be a JSON array');

        $this->mapper->map(['candles' => 'broken']);
    }

    public function testMapCandleItemOfWrongTypeThrows(): void
    {
        $this->expectException(TInvestResponseException::class);
        $this->expectExceptionMessage('each candle must be a JSON object');

        $this->mapper->map(['candles' => ['broken']]);
    }

    public static function provideMissingRequiredFields(): iterable
    {
        yield 'open' => ['open'];
        yield 'high' => ['high'];
        yield 'low' => ['low'];
        yield 'close' => ['close'];
        yield 'volume' => ['volume'];
        yield 'time' => ['time'];
        yield 'isComplete' => ['isComplete'];
    }

    #[DataProvider('provideMissingRequiredFields')]
    public function testMapMissingRequiredFieldThrows(string $field): void
    {
        $data = [
            'candles' => [$this->fullCandle()],
        ];
        unset($data['candles'][0][$field]);

        $this->expectException(TInvestResponseException::class);
        $this->expectExceptionMessage(sprintf('candle field "%s"', $field));

        $this->mapper->map($data);
    }

    public function testMapDefectiveQuotationFieldThrows(): void
    {
        $data = [
            'candles' => [array_merge($this->fullCandle(), ['open' => '100'])],
        ];

        $this->expectException(TInvestResponseException::class);
        $this->expectExceptionMessage('candle field "open" must be a JSON object');

        $this->mapper->map($data);
    }

    public function testMapCandles(): void
    {
        $data = [
            'candles' => [
                $this->fullCandle(),
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
        $this->assertSame('2024-01-15T10:00:00+00:00', $candle->time->format(DATE_ATOM));
        $this->assertTrue($candle->isComplete);
    }

    public function testMapMultipleCandles(): void
    {
        $second = $this->fullCandle();
        $second['open'] = ['units' => '103', 'nano' => 0];
        $second['high'] = ['units' => '108', 'nano' => 0];
        $second['low'] = ['units' => '102', 'nano' => 0];
        $second['close'] = ['units' => '107', 'nano' => 0];
        $second['volume'] = 1500;
        $second['time'] = '2024-01-15T10:01:00Z';
        $second['isComplete'] = false;

        $data = [
            'candles' => [
                $this->fullCandle(),
                $second,
            ],
        ];

        $result = $this->mapper->map($data);

        $this->assertCount(2, $result->candles);
        $this->assertFalse($result->candles[1]->isComplete);
    }

    public function testMapQuotationUnitsAndNanoMissingFallbackToZero(): void
    {
        $data = [
            'candles' => [array_merge($this->fullCandle(), ['open' => []])],
        ];

        $result = $this->mapper->map($data);

        $this->assertSame(0.0, $result->candles[0]->open->value);
        $this->assertSame(0, $result->candles[0]->open->units);
        $this->assertSame(0, $result->candles[0]->open->nano);
    }

    public function testMapQuotationNanoMissingKeepsUnitsPart(): void
    {
        $data = [
            'candles' => [array_merge($this->fullCandle(), ['open' => ['units' => '100']])],
        ];

        $result = $this->mapper->map($data);

        $this->assertSame(100.0, $result->candles[0]->open->value);
        $this->assertSame(100, $result->candles[0]->open->units);
        $this->assertSame(0, $result->candles[0]->open->nano);
    }

    public function testMapQuotationUnitsMissingKeepsNanoPart(): void
    {
        $data = [
            'candles' => [array_merge($this->fullCandle(), ['open' => ['nano' => 500000000]])],
        ];

        $result = $this->mapper->map($data);

        $this->assertSame(0.5, $result->candles[0]->open->value);
        $this->assertSame(0, $result->candles[0]->open->units);
        $this->assertSame(500000000, $result->candles[0]->open->nano);
    }

    public function testMapKeepsOptionalVolumeBuySellNullable(): void
    {
        $withVolumes = array_merge($this->fullCandle(), ['volumeBuy' => 700, 'volumeSell' => 300]);
        $withoutVolumes = $this->fullCandle();

        $data = [
            'candles' => [$withVolumes, $withoutVolumes],
        ];

        $result = $this->mapper->map($data);

        $this->assertSame(700, $result->candles[0]->volumeBuy);
        $this->assertSame(300, $result->candles[0]->volumeSell);
        $this->assertNull($result->candles[1]->volumeBuy);
        $this->assertNull($result->candles[1]->volumeSell);
    }

    private function fullCandle(): array
    {
        return [
            'open' => ['units' => '100', 'nano' => 0],
            'high' => ['units' => '105', 'nano' => 0],
            'low' => ['units' => '99', 'nano' => 0],
            'close' => ['units' => '103', 'nano' => 0],
            'volume' => 1000,
            'time' => '2024-01-15T10:00:00Z',
            'isComplete' => true,
        ];
    }
}
