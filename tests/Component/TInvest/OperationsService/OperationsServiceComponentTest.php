<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\OperationsService;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\GetPortfolioResponseMapper;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\GetPositionsResponseMapper;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\OperationMapper;
use TInvest\Core\Component\TInvest\OperationsService\OperationsServiceComponent;
use TInvest\Core\Component\TInvest\Shared\Exception\TInvestRequestException;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\PercentFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuantityFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;
use TypeError;
use UnexpectedValueException;

final class OperationsServiceComponentTest extends TestCase
{
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
    }

    private function createComponent(): OperationsServiceComponent
    {
        $client = new Client(['handler' => HandlerStack::create($this->mockHandler)]);

        return new OperationsServiceComponent(
            'test-token',
            'test-account',
            'https://invest-public-api.test.local/',
            $client,
            new NullLogger(),
            new GetPortfolioResponseMapper(
                new MoneyFactory(),
                new PercentFactory(),
                new QuantityFactory(),
                new QuotationFactory(),
            ),
            new OperationMapper(new MoneyFactory()),
            new GetPositionsResponseMapper(new MoneyFactory()),
        );
    }

    private function queueResponse(string $body): void
    {
        $this->mockHandler->append(new Response(200, ['Content-Type' => 'application/json'], $body));
    }

    public function testGetPortfolioEmptyBodyThrows(): void
    {
        $this->queueResponse('');
        $component = $this->createComponent();

        $this->expectException(TInvestRequestException::class);
        $this->expectExceptionMessage('empty response body');

        $component->getPortfolio();
    }

    public function testGetPortfolioMissingFieldThrows(): void
    {
        $this->queueResponse('{"unrelated": true}');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('positions');

        $component->getPortfolio();
    }

    public function testGetPortfolioNullPositionsThrows(): void
    {
        $this->queueResponse('{"positions": null}');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('positions');

        $component->getPortfolio();
    }

    public function testGetPortfolioFieldOfWrongTypeThrows(): void
    {
        $this->queueResponse('{"positions": "broken"}');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON array');

        $component->getPortfolio();
    }

    public function testGetPortfolioItemOfWrongTypeThrows(): void
    {
        $this->queueResponse('{"positions": ["broken"]}');
        $component = $this->createComponent();

        $portfolio = $component->getPortfolio();

        // Элементы списка проверяются лениво — при итерации.
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('each item');

        $portfolio->positions->rewind();
    }

    public function testGetPortfolioBodyIsNotJsonObjectThrows(): void
    {
        $this->queueResponse('null');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON object');

        $component->getPortfolio();
    }

    public function testGetPortfolioTopLevelArrayThrows(): void
    {
        $this->queueResponse('[{"positions": []}]');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON object');

        $component->getPortfolio();
    }

    public function testGetPortfolioEmptyObjectInsteadOfArrayThrows(): void
    {
        $this->queueResponse('{"positions": {}}');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON array');

        $component->getPortfolio();
    }

    public function testGetPortfolioInvalidJsonThrows(): void
    {
        $this->queueResponse('{"positions": ');
        $component = $this->createComponent();

        $this->expectException(JsonException::class);

        $component->getPortfolio();
    }

    public function testGetPortfolioEmptyListIsValidEmptyResponse(): void
    {
        $this->queueResponse('{"positions": []}');
        $component = $this->createComponent();

        $result = $component->getPortfolio();

        $this->assertSame([], iterator_to_array($result->positions, false));
    }

    public function testGetPortfolioMapsPayload(): void
    {
        $this->queueResponse(
            <<<'JSON'
            {
                "totalAmountPortfolio": {"currency": "RUB", "units": "1000", "nano": 500000000},
                "positions": [
                    {
                        "figi": "BBG000000001",
                        "instrumentType": "share",
                        "quantity": {"units": "10", "nano": 500000000},
                        "averagePositionPrice": null,
                        "expectedYield": {"units": "5", "nano": 250000000},
                        "currentNkd": null,
                        "currentPrice": {"currency": "RUB", "units": "105", "nano": 0},
                        "averagePositionPricePt": null,
                        "averagePositionPriceFifo": {"currency": "RUB", "units": "100", "nano": 0},
                        "quantityLots": {"units": "1", "nano": 0},
                        "blocked": true,
                        "blockedLots": null,
                        "positionUid": "pos-1",
                        "instrumentUid": "inst-1",
                        "varMargin": {"currency": "RUB", "units": "0", "nano": 0},
                        "expectedYieldFifo": {"units": "5", "nano": 0},
                        "ticker": "SBER"
                    }
                ]
            }
            JSON
        );
        $component = $this->createComponent();

        $result = $component->getPortfolio();

        $this->assertNotNull($result->totalAmountPortfolio);
        $this->assertSame(1000.5, $result->totalAmountPortfolio->value);

        $positions = iterator_to_array($result->positions, false);
        $this->assertCount(1, $positions);
        $position = $positions[0];
        $this->assertSame('BBG000000001', $position->figi);
        $this->assertSame(10.5, $position->quantity->value);
        $this->assertNull($position->averagePositionPrice);
        $this->assertSame(5.25, $position->expectedYield->value);
        $this->assertNull($position->currentNkd);
        $this->assertSame(105.0, $position->currentPrice->value);
        $this->assertNull($position->averagePositionPricePt);
        $this->assertSame(100.0, $position->averagePositionPriceFifo->value);
        $this->assertSame(1.0, $position->quantityLots->value);
        $this->assertTrue($position->blocked);
        $this->assertNull($position->blockedLots);
        $this->assertSame('SBER', $position->ticker);
    }

    public function testGetPositionsEmptyBodyThrows(): void
    {
        $this->queueResponse('');
        $component = $this->createComponent();

        $this->expectException(TInvestRequestException::class);
        $this->expectExceptionMessage('empty response body');

        $component->getPositions();
    }

    public function testGetPositionsBodyIsNotJsonObjectThrows(): void
    {
        $this->queueResponse('null');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON object');

        $component->getPositions();
    }

    public function testGetPositionsTopLevelArrayThrows(): void
    {
        $this->queueResponse('[{"money": []}]');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON object');

        $component->getPositions();
    }

    public function testGetPositionsInvalidJsonThrows(): void
    {
        $this->queueResponse('{"money": ');
        $component = $this->createComponent();

        $this->expectException(JsonException::class);

        $component->getPositions();
    }

    public function testGetPositionsEmptyObjectIsValidEmptyResponse(): void
    {
        $this->queueResponse('{}');
        $component = $this->createComponent();

        $result = $component->getPositions();

        $this->assertSame([], $result->money);
        $this->assertNull($result->blocked);
        $this->assertSame([], $result->securities);
        $this->assertFalse($result->limitsLoadingInProgress);
        $this->assertSame([], $result->futures);
        $this->assertSame([], $result->options);
    }

    public function testGetPositionsMapsPayload(): void
    {
        $this->queueResponse(
            <<<'JSON'
            {
                "money": [
                    {"currency": "rub", "units": "1000", "nano": 500000000}
                ],
                "blocked": [
                    {"currency": "rub", "units": "250", "nano": 0}
                ],
                "securities": [
                    {
                        "figi": "BBG000000001",
                        "blocked": 5,
                        "balance": 100,
                        "positionUid": "pos-1",
                        "instrumentUid": "inst-1",
                        "exchangeBlocked": false,
                        "instrumentType": "share"
                    }
                ],
                "limitsLoadingInProgress": true,
                "futures": [
                    {
                        "figi": "FUT000000001",
                        "blocked": 1,
                        "balance": 3,
                        "positionUid": "fpos-1",
                        "instrumentUid": "finst-1"
                    }
                ],
                "options": [
                    {
                        "positionUid": "opos-1",
                        "instrumentUid": "oinst-1",
                        "blocked": 2,
                        "balance": 7
                    }
                ]
            }
            JSON
        );
        $component = $this->createComponent();

        $result = $component->getPositions();

        $this->assertCount(1, $result->money);
        $this->assertSame('rub', $result->money[0]->currency);
        $this->assertSame(1000.5, $result->money[0]->value);

        $this->assertNotNull($result->blocked);
        $this->assertCount(1, $result->blocked);
        $this->assertSame(250.0, $result->blocked[0]->value);

        $this->assertCount(1, $result->securities);
        $security = $result->securities[0];
        $this->assertSame('BBG000000001', $security->figi);
        $this->assertSame(5, $security->blocked);
        $this->assertSame(100, $security->balance);
        $this->assertSame('pos-1', $security->positionUid);
        $this->assertSame('inst-1', $security->instrumentUid);
        $this->assertFalse($security->exchangeBlocked);
        $this->assertSame('share', $security->instrumentType);

        $this->assertTrue($result->limitsLoadingInProgress);

        $this->assertCount(1, $result->futures);
        $future = $result->futures[0];
        $this->assertSame('FUT000000001', $future->figi);
        $this->assertSame(3, $future->balance);

        $this->assertCount(1, $result->options);
        $option = $result->options[0];
        $this->assertSame('opos-1', $option->positionUid);
        $this->assertSame(7, $option->balance);
    }

    /**
     * Построчная семантика итератора: корректная первая позиция доступна
     * до падения маппинга второй — дефект ответа не маскируется пустым
     * портфелем и не скрывает уже прочитанные позиции.
     */
    public function testGetPortfolioInvalidSecondPositionDoesNotHideFirstPosition(): void
    {
        $this->queueResponse(
            <<<'JSON'
            {
                "positions": [
                    {
                        "figi": "BBG000000001",
                        "instrumentType": "share",
                        "quantity": {"units": "10", "nano": 0},
                        "averagePositionPrice": null,
                        "expectedYield": {"units": "5", "nano": 0},
                        "currentNkd": null,
                        "currentPrice": {"currency": "RUB", "units": "105", "nano": 0},
                        "averagePositionPricePt": null,
                        "averagePositionPriceFifo": {"currency": "RUB", "units": "100", "nano": 0},
                        "quantityLots": {"units": "1", "nano": 0},
                        "blocked": false,
                        "blockedLots": null,
                        "positionUid": "pos-1",
                        "instrumentUid": "inst-1",
                        "varMargin": {"currency": "RUB", "units": "0", "nano": 0},
                        "expectedYieldFifo": {"units": "5", "nano": 0},
                        "ticker": "SBER"
                    },
                    {
                        "figi": "BBG000000002",
                        "instrumentType": "share",
                        "quantity": null,
                        "averagePositionPrice": null,
                        "expectedYield": {"units": "5", "nano": 0},
                        "currentNkd": null,
                        "currentPrice": {"currency": "RUB", "units": "105", "nano": 0},
                        "averagePositionPricePt": null,
                        "averagePositionPriceFifo": {"currency": "RUB", "units": "100", "nano": 0},
                        "quantityLots": {"units": "1", "nano": 0},
                        "blocked": false,
                        "blockedLots": null,
                        "positionUid": "pos-2",
                        "instrumentUid": "inst-2",
                        "varMargin": {"currency": "RUB", "units": "0", "nano": 0},
                        "expectedYieldFifo": {"units": "5", "nano": 0},
                        "ticker": "GAZP"
                    }
                ]
            }
            JSON
        );
        $component = $this->createComponent();

        $portfolio = $component->getPortfolio();

        $iterator = $portfolio->positions;
        $iterator->rewind();

        $this->assertSame('BBG000000001', $iterator->current()->figi);

        // Падение происходит в момент продвижения итератора: next()
        // выполняет ленивый маппинг второй позиции.
        $this->expectException(TypeError::class);
        $iterator->next();
    }
}
