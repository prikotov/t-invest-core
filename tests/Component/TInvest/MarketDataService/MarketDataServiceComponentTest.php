<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\MarketDataService;

use DateTimeImmutable;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetCandlesRequestDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetLastPricesRequestDto;
use TInvest\Core\Component\TInvest\MarketDataService\Enum\CandleIntervalEnum;
use TInvest\Core\Component\TInvest\MarketDataService\Exception\TInvestBrokerException;
use TInvest\Core\Component\TInvest\MarketDataService\MarketDataServiceComponent;
use TInvest\Core\Component\TInvest\MarketDataService\Mapper\CandleMapper;
use TInvest\Core\Component\TInvest\MarketDataService\Mapper\LastPriceMapper;
use TInvest\Core\Component\TInvest\MarketDataService\Mapper\OrderBookMapper;
use TInvest\Core\Component\TInvest\Shared\Exception\TInvestRequestException;
use TInvest\Core\Component\TInvest\Shared\Exception\TInvestResponseException;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;
use UnexpectedValueException;

final class MarketDataServiceComponentTest extends TestCase
{
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
    }

    private function createComponent(): MarketDataServiceComponent
    {
        $client = new Client(['handler' => HandlerStack::create($this->mockHandler)]);

        return new MarketDataServiceComponent(
            'test-token',
            'https://invest-public-api.test.local/',
            $client,
            new NullLogger(),
            new CandleMapper(new QuotationFactory()),
            new LastPriceMapper(new QuotationFactory()),
            new OrderBookMapper(new QuotationFactory()),
        );
    }

    private function queueResponse(string $body): void
    {
        $this->mockHandler->append(new Response(200, ['Content-Type' => 'application/json'], $body));
    }

    private function createRequest(): GetLastPricesRequestDto
    {
        return new GetLastPricesRequestDto(['BBG000000001']);
    }

    private function createCandlesRequest(?int $limit = null): GetCandlesRequestDto
    {
        return new GetCandlesRequestDto(
            'instrument-uid-1',
            new DateTimeImmutable('2024-01-01T00:00:00+03:00'),
            new DateTimeImmutable('2024-01-31T00:00:00+03:00'),
            CandleIntervalEnum::DAY,
            $limit,
        );
    }

    private function candlesPayload(): string
    {
        return <<<'JSON'
        {
            "candles": [
                {
                    "open": {"units": "100", "nano": 0},
                    "high": {"units": "105", "nano": 0},
                    "low": {"units": "99", "nano": 0},
                    "close": {"units": "103", "nano": 500000000},
                    "volume": 1000,
                    "time": "2024-01-15T10:00:00Z",
                    "isComplete": true
                }
            ]
        }
        JSON;
    }

    public function testGetCandlesSendsBodyWithoutLimitWhenLimitIsNull(): void
    {
        $this->queueResponse($this->candlesPayload());
        $component = $this->createComponent();

        $component->getCandles($this->createCandlesRequest());

        $request = $this->mockHandler->getLastRequest();
        self::assertNotNull($request);
        self::assertSame([
            'instrumentId' => 'instrument-uid-1',
            'from' => '2024-01-01T00:00:00+03:00',
            'to' => '2024-01-31T00:00:00+03:00',
            'interval' => 'CANDLE_INTERVAL_DAY',
        ], json_decode((string)$request->getBody(), true));
    }

    public function testGetCandlesSendsLimitWhenNotNull(): void
    {
        $this->queueResponse($this->candlesPayload());
        $component = $this->createComponent();

        $component->getCandles($this->createCandlesRequest(50));

        $request = $this->mockHandler->getLastRequest();
        self::assertNotNull($request);
        self::assertSame([
            'instrumentId' => 'instrument-uid-1',
            'from' => '2024-01-01T00:00:00+03:00',
            'to' => '2024-01-31T00:00:00+03:00',
            'interval' => 'CANDLE_INTERVAL_DAY',
            'limit' => 50,
        ], json_decode((string)$request->getBody(), true));
    }

    public function testGetCandlesMapsPayload(): void
    {
        $this->queueResponse($this->candlesPayload());
        $component = $this->createComponent();

        $result = $component->getCandles($this->createCandlesRequest());

        self::assertCount(1, $result->candles);
        self::assertSame(103.5, $result->candles[0]->close->value);
        self::assertSame(1000, $result->candles[0]->volume);
        self::assertTrue($result->candles[0]->isComplete);
    }

    public function testGetCandlesEmptyBodyThrows(): void
    {
        $this->queueResponse('');
        $component = $this->createComponent();

        $this->expectException(TInvestRequestException::class);
        $this->expectExceptionMessage('GetCandles: empty response body (HTTP 200).');

        $component->getCandles($this->createCandlesRequest());
    }

    public function testGetCandlesInvalidJsonThrowsTypedNotJsonException(): void
    {
        $this->queueResponse('{"candles": ');
        $component = $this->createComponent();

        try {
            $component->getCandles($this->createCandlesRequest());
            self::fail('TInvestResponseException expected');
        } catch (JsonException $e) {
            self::fail('JsonException must not leak out: ' . $e->getMessage());
        } catch (TInvestResponseException $e) {
            self::assertStringContainsString('not valid JSON', $e->getMessage());
        }
    }

    public function testGetCandlesNullBodyThrows(): void
    {
        $this->queueResponse('null');
        $component = $this->createComponent();

        $this->expectException(TInvestResponseException::class);
        $this->expectExceptionMessage('must be a JSON array');

        $component->getCandles($this->createCandlesRequest());
    }

    public function testGetCandlesScalarBodyThrows(): void
    {
        $this->queueResponse('42');
        $component = $this->createComponent();

        $this->expectException(TInvestResponseException::class);
        $this->expectExceptionMessage('must be a JSON array');

        $component->getCandles($this->createCandlesRequest());
    }

    public function testGetCandlesBrokerError50002ThrowsTyped(): void
    {
        $this->queueResponse(
            '{"code":5,"description":"50002","message":"GetCandles: Instrument not found for uid=instrument-uid-1"}',
        );
        $component = $this->createComponent();

        try {
            $component->getCandles($this->createCandlesRequest());
            self::fail('TInvestBrokerException expected');
        } catch (TInvestBrokerException $e) {
            self::assertSame(5, $e->brokerCode);
            self::assertSame('50002', $e->description);
            self::assertStringContainsStringIgnoringCase('instrument not found', $e->brokerMessage);
        }
    }

    public function testGetCandlesBrokerError50003ThrowsTyped(): void
    {
        $this->queueResponse(
            '{"code":5,"description":"50003","message":"GetCandles: requested interval is unsupported"}',
        );
        $component = $this->createComponent();

        try {
            $component->getCandles($this->createCandlesRequest());
            self::fail('TInvestBrokerException expected');
        } catch (TInvestBrokerException $e) {
            self::assertSame(5, $e->brokerCode);
            self::assertSame('50003', $e->description);
            self::assertStringContainsString('unsupported', $e->brokerMessage);
        }
    }

    public function testGetCandlesBrokerErrorWithStringCodeKeepsStrictType(): void
    {
        $this->queueResponse('{"code":"5","description":"50002","message":"instrument not found"}');
        $component = $this->createComponent();

        try {
            $component->getCandles($this->createCandlesRequest());
            self::fail('TInvestBrokerException expected');
        } catch (TInvestBrokerException $e) {
            self::assertSame('5', $e->brokerCode);
            self::assertSame('50002', $e->description);
        }
    }

    public function testGetCandlesBrokerErrorMessageIsOptional(): void
    {
        $this->queueResponse('{"code":16,"description":"unavailable"}');
        $component = $this->createComponent();

        try {
            $component->getCandles($this->createCandlesRequest());
            self::fail('TInvestBrokerException expected');
        } catch (TInvestBrokerException $e) {
            self::assertSame(16, $e->brokerCode);
            self::assertSame('unavailable', $e->description);
            self::assertSame('', $e->brokerMessage);
        }
    }

    public function testGetCandlesWithoutCandlesKeyThrows(): void
    {
        $this->queueResponse('{"unrelated": true}');
        $component = $this->createComponent();

        $this->expectException(TInvestResponseException::class);
        $this->expectExceptionMessage('must contain a "candles" array');

        $component->getCandles($this->createCandlesRequest());
    }

    public function testGetCandlesJsonListThrows(): void
    {
        $this->queueResponse('[]');
        $component = $this->createComponent();

        $this->expectException(TInvestResponseException::class);
        $this->expectExceptionMessage('must contain a "candles" array');

        $component->getCandles($this->createCandlesRequest());
    }

    public function testGetCandlesBrokerCodeWithoutDescriptionThrows(): void
    {
        $this->queueResponse('{"code": 5}');
        $component = $this->createComponent();

        $this->expectException(TInvestResponseException::class);
        $this->expectExceptionMessage('must contain a "candles" array');

        $component->getCandles($this->createCandlesRequest());
    }

    public function testGetCandlesEmptyCandlesListIsValidEmptyResponse(): void
    {
        $this->queueResponse('{"candles": []}');
        $component = $this->createComponent();

        $result = $component->getCandles($this->createCandlesRequest());

        self::assertSame([], $result->candles);
    }

    public function testGetCandlesBrokerErrorTakesPriorityOverCandlesKey(): void
    {
        $this->queueResponse(
            '{"candles":[],"code":5,"description":"50002","message":"GetCandles: instrument not found"}',
        );
        $component = $this->createComponent();

        try {
            $component->getCandles($this->createCandlesRequest());
            self::fail('TInvestBrokerException expected');
        } catch (TInvestBrokerException $e) {
            self::assertSame(5, $e->brokerCode);
            self::assertSame('50002', $e->description);
            self::assertStringContainsStringIgnoringCase('instrument not found', $e->brokerMessage);
        }
    }

    public function testGetLastPricesEmptyBodyThrows(): void
    {
        $this->queueResponse('');
        $component = $this->createComponent();

        $this->expectException(TInvestRequestException::class);
        $this->expectExceptionMessage('empty response body');

        $component->getLastPrices($this->createRequest());
    }

    public function testGetLastPricesMissingFieldThrows(): void
    {
        $this->queueResponse('{"unrelated": true}');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('lastPrices');

        $component->getLastPrices($this->createRequest());
    }

    public function testGetLastPricesFieldOfWrongTypeThrows(): void
    {
        $this->queueResponse('{"lastPrices": "broken"}');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON array');

        $component->getLastPrices($this->createRequest());
    }

    public function testGetLastPricesItemOfWrongTypeThrows(): void
    {
        $this->queueResponse('{"lastPrices": ["broken"]}');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('each item');

        $component->getLastPrices($this->createRequest());
    }

    public function testGetLastPricesBodyIsNotJsonObjectThrows(): void
    {
        $this->queueResponse('null');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON object');

        $component->getLastPrices($this->createRequest());
    }

    public function testGetLastPricesTopLevelArrayThrows(): void
    {
        $this->queueResponse('[{"lastPrices": []}]');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON object');

        $component->getLastPrices($this->createRequest());
    }

    public function testGetLastPricesEmptyObjectInsteadOfArrayThrows(): void
    {
        $this->queueResponse('{"lastPrices": {}}');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON array');

        $component->getLastPrices($this->createRequest());
    }

    public function testGetLastPricesObjectWithItemInsteadOfArrayThrows(): void
    {
        $this->queueResponse(
            <<<'JSON'
            {
                "lastPrices": {
                    "BBG000000001": {
                        "figi": "BBG000000001",
                        "price": {"units": "100", "nano": 500000000}
                    }
                }
            }
            JSON
        );
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON array');

        $component->getLastPrices($this->createRequest());
    }

    public function testGetLastPricesInvalidJsonThrows(): void
    {
        $this->queueResponse('{"lastPrices": ');
        $component = $this->createComponent();

        $this->expectException(JsonException::class);

        $component->getLastPrices($this->createRequest());
    }

    public function testGetLastPricesEmptyListIsValidEmptyResponse(): void
    {
        $this->queueResponse('{"lastPrices": []}');
        $component = $this->createComponent();

        $result = $component->getLastPrices($this->createRequest());

        $this->assertSame([], $result->lastPrices);
    }

    public function testGetLastPricesMapsPayload(): void
    {
        $this->queueResponse(
            <<<'JSON'
            {
                "lastPrices": [
                    {
                        "figi": "BBG000000001",
                        "price": {"units": "100", "nano": 500000000},
                        "time": "2024-01-15T10:00:00Z",
                        "ticker": "SBER",
                        "classCode": "TQBR",
                        "instrumentUid": "instrument-1"
                    }
                ]
            }
            JSON
        );
        $component = $this->createComponent();

        $result = $component->getLastPrices($this->createRequest());

        $this->assertCount(1, $result->lastPrices);
        $lastPrice = $result->lastPrices[0];
        $this->assertSame('BBG000000001', $lastPrice->figi);
        $this->assertSame(100.5, $lastPrice->price->value);
        $this->assertSame('SBER', $lastPrice->ticker);
        $this->assertSame('TQBR', $lastPrice->classCode);
        $this->assertSame('instrument-1', $lastPrice->instrumentUid);
    }
}
