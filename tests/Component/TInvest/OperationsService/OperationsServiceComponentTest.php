<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\OperationsService;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use TInvest\Core\Component\TInvest\OperationsService\Dto\GetOperationsByCursorRequestDto;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\GetOperationsByCursorRequestMapper;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\GetOperationsByCursorResponseMapper;
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

    private function createComponent(?array &$history = null): OperationsServiceComponent
    {
        $stack = HandlerStack::create($this->mockHandler);
        if ($history !== null) {
            $stack->push(Middleware::history($history));
        }
        $client = new Client(['handler' => $stack]);

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
            new GetOperationsByCursorRequestMapper(),
            new GetOperationsByCursorResponseMapper(new MoneyFactory(), new QuotationFactory()),
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

    private function queueJsonResponse(array $payload): void
    {
        $this->queueResponse((string) json_encode($payload));
    }

    private function operationsByCursorPagePayload(): array
    {
        return [
            'items' => [
                [
                    'cursor' => 'op-cursor-1',
                    'brokerAccountId' => '2019246368',
                    'id' => 'operation-1',
                    'parentOperationId' => 'parent-1',
                    'name' => 'Покупка',
                    'date' => '2024-01-15T10:30:00Z',
                    'type' => 'OPERATION_TYPE_BUY',
                    'description' => 'Покупка акций',
                    'state' => 'OPERATION_STATE_EXECUTED',
                    'instrumentUid' => 'inst-uid-1',
                    'figi' => 'BBG000000001',
                    'instrumentType' => 'share',
                    'instrumentKind' => 'INSTRUMENT_KIND_SHARE',
                    'payment' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 500000000],
                    'price' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
                    'commission' => ['currency' => 'RUB', 'units' => '1', 'nano' => 50000000],
                    'yieldRelative' => ['units' => '5', 'nano' => 0],
                    'accruedInt' => ['currency' => 'RUB', 'units' => '0', 'nano' => 0],
                    'quantity' => '10',
                    'quantityRest' => '0',
                    'quantityDone' => '10',
                    'cancelReason' => '',
                    'tradesInfo' => [
                        'trades' => [
                            [
                                'num' => 'trade-1',
                                'date' => '2024-01-15T10:30:00Z',
                                'quantity' => '10',
                                'price' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
                                'yield' => ['currency' => 'RUB', 'units' => '0', 'nano' => 0],
                                'yieldRelative' => ['units' => '0', 'nano' => 0],
                            ],
                        ],
                    ],
                    'assetUid' => 'asset-uid-1',
                ],
            ],
            'hasNext' => true,
            'nextCursor' => 'cursor-2',
        ];
    }

    public function testGetOperationsByCursorMapsSuccessfulPage(): void
    {
        $history = [];
        $this->queueJsonResponse($this->operationsByCursorPagePayload());
        $component = $this->createComponent($history);

        $request = new GetOperationsByCursorRequestDto(
            accountId: '2019246368',
            cursor: 'cursor-1',
            limit: 500,
        );

        $result = $component->getOperationsByCursor($request);

        $this->assertCount(1, $history);
        $sentRequest = $history[0]['request'];
        $this->assertStringEndsWith(
            'tinkoff.public.invest.api.contract.v1.OperationsService/GetOperationsByCursor',
            (string) $sentRequest->getUri(),
        );
        $sentBody = json_decode((string) $sentRequest->getBody(), true);
        $this->assertSame('2019246368', $sentBody['accountId']);
        $this->assertSame('cursor-1', $sentBody['cursor']);
        $this->assertSame(500, $sentBody['limit']);

        $this->assertTrue($result->hasNext);
        $this->assertSame('cursor-2', $result->nextCursor);
        $this->assertCount(1, $result->items);

        $item = $result->items[0];
        $this->assertSame('op-cursor-1', $item->cursor);
        $this->assertSame('2019246368', $item->brokerAccountId);
        $this->assertSame('operation-1', $item->id);
        $this->assertSame('parent-1', $item->parentOperationId);
        $this->assertSame('Покупка', $item->name);
        $this->assertSame('2024-01-15T10:30:00+00:00', $item->date->format('c'));
        $this->assertSame('OPERATION_TYPE_BUY', $item->type);
        $this->assertSame('Покупка акций', $item->description);
        $this->assertSame('OPERATION_STATE_EXECUTED', $item->state);
        $this->assertSame('inst-uid-1', $item->instrumentUid);
        $this->assertSame('BBG000000001', $item->figi);
        $this->assertSame('share', $item->instrumentType);
        $this->assertSame('INSTRUMENT_KIND_SHARE', $item->instrumentKind);

        $this->assertNotNull($item->payment);
        $this->assertSame('RUB', $item->payment->currency);
        $this->assertSame(1000.5, $item->payment->value);
        $this->assertNotNull($item->price);
        $this->assertSame(100.0, $item->price->value);
        $this->assertNotNull($item->commission);
        $this->assertSame(1.05, $item->commission->value);
        $this->assertNull($item->yield);
        $this->assertNotNull($item->yieldRelative);
        $this->assertSame(5.0, $item->yieldRelative->value);
        $this->assertNotNull($item->accruedInt);
        $this->assertSame(0.0, $item->accruedInt->value);

        $this->assertSame(10, $item->quantity);
        $this->assertSame(0, $item->quantityRest);
        $this->assertSame(10, $item->quantityDone);
        $this->assertNull($item->cancelDateTime);
        $this->assertSame('', $item->cancelReason);

        $this->assertNotNull($item->trades);
        $this->assertCount(1, $item->trades);
        $trade = $item->trades[0];
        $this->assertSame('trade-1', $trade->num);
        $this->assertSame('2024-01-15T10:30:00+00:00', $trade->date->format('c'));
        $this->assertSame(10, $trade->quantity);
        $this->assertNotNull($trade->price);
        $this->assertSame(100.0, $trade->price->value);
        $this->assertNotNull($trade->yield);
        $this->assertSame(0.0, $trade->yield->value);
        $this->assertNotNull($trade->yieldRelative);
        $this->assertSame(0.0, $trade->yieldRelative->value);

        $this->assertSame('asset-uid-1', $item->assetUid);
    }

    public function testGetOperationsByCursorEmptyItemsIsValidResponse(): void
    {
        $this->queueJsonResponse(['items' => [], 'hasNext' => false]);
        $this->queueJsonResponse(['hasNext' => false]);
        $component = $this->createComponent();

        $request = new GetOperationsByCursorRequestDto(accountId: '2019246368');

        $withEmptyItems = $component->getOperationsByCursor($request);
        $this->assertSame([], $withEmptyItems->items);
        $this->assertFalse($withEmptyItems->hasNext);
        $this->assertNull($withEmptyItems->nextCursor);

        // Proto3 JSON не сериализует пустые списки: отсутствие ключа
        // "items" — тоже валидная пустая страница.
        $withoutItemsKey = $component->getOperationsByCursor($request);
        $this->assertSame([], $withoutItemsKey->items);
        $this->assertFalse($withoutItemsKey->hasNext);
        $this->assertNull($withoutItemsKey->nextCursor);
    }

    public function testGetOperationsByCursorHasNextWithoutNextCursorReturnsAsData(): void
    {
        $this->queueJsonResponse(['items' => [], 'hasNext' => true]);
        $component = $this->createComponent();

        $result = $component->getOperationsByCursor(
            new GetOperationsByCursorRequestDto(accountId: '2019246368'),
        );

        // Различимое состояние: потребитель сам решает, как трактовать
        // страницу без курсора продолжения — исключение не бросается.
        $this->assertTrue($result->hasNext);
        $this->assertNull($result->nextCursor);
        $this->assertSame([], $result->items);
    }

    public function testGetOperationsByCursorHttpErrorThrowsBadResponseException(): void
    {
        $this->mockHandler->append(
            new Response(400, ['Content-Type' => 'application/json'], (string) json_encode(['message' => 'broken'])),
        );
        $component = $this->createComponent();

        $this->expectException(BadResponseException::class);
        $this->expectExceptionMessage('400 Bad Request');

        $component->getOperationsByCursor(new GetOperationsByCursorRequestDto(accountId: '2019246368'));
    }

    public function testGetOperationsByCursorEmptyBodyThrows(): void
    {
        $this->queueResponse('');
        $component = $this->createComponent();

        $this->expectException(TInvestRequestException::class);
        $this->expectExceptionMessage('GetOperationsByCursor: empty response body');

        $component->getOperationsByCursor(new GetOperationsByCursorRequestDto(accountId: '2019246368'));
    }

    public function testGetOperationsByCursorBodyIsNotJsonObjectThrows(): void
    {
        $this->queueResponse('null');
        $component = $this->createComponent();

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a JSON object');

        $component->getOperationsByCursor(new GetOperationsByCursorRequestDto(accountId: '2019246368'));
    }
}
