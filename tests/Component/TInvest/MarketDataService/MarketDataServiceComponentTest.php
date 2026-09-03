<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\MarketDataService;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use RuntimeException;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetLastPricesRequestDto;
use TInvest\Core\Component\TInvest\MarketDataService\MarketDataServiceComponent;
use TInvest\Core\Component\TInvest\MarketDataService\Mapper\CandleMapper;
use TInvest\Core\Component\TInvest\MarketDataService\Mapper\LastPriceMapper;
use TInvest\Core\Component\TInvest\MarketDataService\Mapper\OrderBookMapper;
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

    public function testGetLastPricesEmptyBodyThrows(): void
    {
        $this->queueResponse('');
        $component = $this->createComponent();

        $this->expectException(RuntimeException::class);
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
