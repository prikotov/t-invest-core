<?php

declare(strict_types=1);

namespace TInvest\Skill\Tests\Component\TInvest\InstrumentsService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Skill\Component\TInvest\InstrumentsService\Mapper\GetDividendsResponseMapper;
use TInvest\Skill\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Skill\Component\TInvest\Shared\Factory\QuotationFactory;

final class GetDividendsResponseMapperTest extends TestCase
{
    public function testMapReturnsDividendDtos(): void
    {
        $json = json_encode([
            'dividends' => [
                [
                    'dividendNet' => ['currency' => 'USD', 'units' => '0', 'nano' => 230000000],
                    'paymentDate' => '2024-03-15T00:00:00Z',
                    'declaredDate' => '2024-02-01T00:00:00Z',
                    'lastBuyDate' => '2024-02-10T00:00:00Z',
                    'dividendType' => 'Regular',
                    'recordDate' => '2024-02-12T00:00:00Z',
                    'regularity' => 'Quarterly',
                    'closePrice' => ['currency' => 'USD', 'units' => '180', 'nano' => 0],
                    'yieldValue' => ['units' => '0', 'nano' => 500000000],
                    'createdAt' => '2024-01-15T00:00:00Z',
                ],
            ],
        ]);

        $mapper = new GetDividendsResponseMapper(new MoneyFactory(), new QuotationFactory());
        $dividends = iterator_to_array($mapper->map($json));

        $this->assertCount(1, $dividends);

        $dividend = $dividends[0];
        $this->assertNotNull($dividend->dividendNet);
        $this->assertSame('USD', $dividend->dividendNet->currency);
        $this->assertSame(0.23, $dividend->dividendNet->value);
        $this->assertSame('Regular', $dividend->dividendType);
        $this->assertSame('Quarterly', $dividend->regularity);
        $this->assertNotNull($dividend->paymentDate);
        $this->assertNotNull($dividend->recordDate);
    }

    public function testMapReturnsEmptyForNoDividends(): void
    {
        $json = json_encode(['dividends' => []]);

        $mapper = new GetDividendsResponseMapper(new MoneyFactory(), new QuotationFactory());
        $dividends = iterator_to_array($mapper->map($json));

        $this->assertCount(0, $dividends);
    }
}
