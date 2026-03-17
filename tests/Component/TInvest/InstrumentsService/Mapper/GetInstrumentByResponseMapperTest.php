<?php

declare(strict_types=1);

namespace TInvest\Skill\Tests\Component\TInvest\InstrumentsService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Skill\Component\TInvest\InstrumentsService\Mapper\GetInstrumentByResponseMapper;
use TInvest\Skill\Component\TInvest\Shared\Factory\QuantityFactory;

final class GetInstrumentByResponseMapperTest extends TestCase
{
    public function testMapReturnsInstrumentDto(): void
    {
        $json = json_encode([
            'instrument' => [
                'figi' => 'BBG000000001',
                'ticker' => 'AAPL',
                'classCode' => 'SPBXM',
                'isin' => 'US0378331005',
                'lot' => 1,
                'currency' => 'USD',
                'klong' => ['units' => '0', 'nano' => 0],
                'kshort' => ['units' => '0', 'nano' => 0],
                'dlong' => ['units' => '0', 'nano' => 0],
                'dshort' => ['units' => '0', 'nano' => 0],
                'dlongMin' => ['units' => '0', 'nano' => 0],
                'dshortMin' => ['units' => '0', 'nano' => 0],
                'shortEnabledFlag' => true,
                'name' => 'Apple Inc.',
                'exchange' => 'SPB',
                'countryOfRisk' => 'US',
                'countryOfRiskName' => 'United States',
                'instrumentType' => 'share',
                'tradingStatus' => 1,
                'otcFlag' => false,
                'buyAvailableFlag' => true,
                'sellAvailableFlag' => true,
                'minPriceIncrement' => ['units' => '0', 'nano' => 10000000],
                'apiTradeAvailableFlag' => true,
                'uid' => 'a5f5f5e0-5c5f-4b5f-8c5f-5f5f5f5f5f5f',
                'realExchange' => 'REAL_EXCHANGE_SPB',
                'positionUid' => 'pos-uid-1',
                'forIisFlag' => false,
                'forQualInvestorFlag' => false,
                'weekendFlag' => false,
                'blockedTcaFlag' => false,
                'instrumentKind' => 'INSTRUMENT_KIND_SHARE',
                'first1minCandleDate' => '2020-01-01T00:00:00Z',
                'first1dayCandleDate' => '2020-01-01T00:00:00Z',
            ],
        ]);

        $mapper = new GetInstrumentByResponseMapper(new QuantityFactory());
        $instrument = $mapper->map($json);

        $this->assertSame('BBG000000001', $instrument->figi);
        $this->assertSame('AAPL', $instrument->ticker);
        $this->assertSame('SPBXM', $instrument->classCode);
        $this->assertSame('US0378331005', $instrument->isin);
        $this->assertSame(1, $instrument->lot);
        $this->assertSame('USD', $instrument->currency);
        $this->assertSame('Apple Inc.', $instrument->name);
        $this->assertSame('SPB', $instrument->exchange);
        $this->assertSame('US', $instrument->countryOfRisk);
        $this->assertTrue($instrument->shortEnabledFlag);
        $this->assertTrue($instrument->buyAvailableFlag);
        $this->assertTrue($instrument->sellAvailableFlag);
        $this->assertNotNull($instrument->first1minCandleDate);
        $this->assertNotNull($instrument->first1dayCandleDate);
    }
}
