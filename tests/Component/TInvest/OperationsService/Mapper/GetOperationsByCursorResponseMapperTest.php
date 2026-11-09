<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\OperationsService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\GetOperationsByCursorResponseMapper;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;
use UnexpectedValueException;

final class GetOperationsByCursorResponseMapperTest extends TestCase
{
    private function createMapper(): GetOperationsByCursorResponseMapper
    {
        return new GetOperationsByCursorResponseMapper(new MoneyFactory(), new QuotationFactory());
    }

    private function operationItemPayload(): array
    {
        return [
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
                        'yield' => ['currency' => 'RUB', 'units' => '1', 'nano' => 0],
                        'yieldRelative' => ['units' => '0', 'nano' => 0],
                    ],
                ],
            ],
            'assetUid' => 'asset-uid-1',
        ];
    }

    public function testMapFullPayload(): void
    {
        $result = $this->createMapper()->map([
            'items' => [$this->operationItemPayload()],
            'hasNext' => true,
            'nextCursor' => 'cursor-2',
        ]);

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
        $this->assertNull($item->accruedInt);

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
        $this->assertSame(1.0, $trade->yield->value);
        $this->assertNotNull($trade->yieldRelative);
        $this->assertSame(0.0, $trade->yieldRelative->value);

        $this->assertSame('asset-uid-1', $item->assetUid);
    }

    public function testMapOmittedOptionalsBecomeNull(): void
    {
        $payload = $this->operationItemPayload();
        $payload['cancelDateTime'] = '2024-01-16T00:00:00Z';
        unset(
            $payload['parentOperationId'],
            $payload['commission'],
            $payload['yieldRelative'],
            $payload['tradesInfo'],
        );

        $result = $this->createMapper()->map(['items' => [$payload], 'hasNext' => false]);

        $item = $result->items[0];
        $this->assertNull($item->parentOperationId);
        $this->assertNull($item->commission);
        $this->assertNull($item->yieldRelative);
        $this->assertNull($item->accruedInt);
        $this->assertNull($item->yield);
        $this->assertNull($item->trades);
        $this->assertNotNull($item->cancelDateTime);
        $this->assertSame('2024-01-16T00:00:00+00:00', $item->cancelDateTime->format('c'));
    }

    public function testMapEmptyItemsIsValidResponse(): void
    {
        $withEmptyItems = $this->createMapper()->map(['items' => [], 'hasNext' => false]);

        $this->assertSame([], $withEmptyItems->items);
        $this->assertFalse($withEmptyItems->hasNext);
        $this->assertNull($withEmptyItems->nextCursor);

        $withoutItemsKey = $this->createMapper()->map(['hasNext' => false]);

        $this->assertSame([], $withoutItemsKey->items);
        $this->assertFalse($withoutItemsKey->hasNext);
        $this->assertNull($withoutItemsKey->nextCursor);
    }

    public function testMapHasNextTrueWithoutNextCursorReturnsAsData(): void
    {
        $result = $this->createMapper()->map(['items' => [], 'hasNext' => true]);

        $this->assertTrue($result->hasNext);
        $this->assertNull($result->nextCursor);
    }

    public function testMapHasNextFalseResetsNextCursor(): void
    {
        $result = $this->createMapper()->map(['items' => [], 'hasNext' => false, 'nextCursor' => 'cursor-x']);

        $this->assertFalse($result->hasNext);
        $this->assertNull($result->nextCursor);
    }

    public function testMapNextCursorOfWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('nextCursor');

        $this->createMapper()->map(['items' => [], 'hasNext' => true, 'nextCursor' => 123]);
    }

    public function testMapItemsOfWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('"items" must be a JSON array');

        $this->createMapper()->map(['items' => 'broken', 'hasNext' => false]);
    }

    public function testMapItemOfWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('each item');

        $this->createMapper()->map(['items' => ['broken'], 'hasNext' => false]);
    }

    public function testMapMissingRequiredFieldThrows(): void
    {
        $payload = $this->operationItemPayload();
        unset($payload['id']);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('field "id" must be a string, missing given');

        $this->createMapper()->map(['items' => [$payload], 'hasNext' => false]);
    }

    public function testMapRequiredFieldOfWrongTypeThrows(): void
    {
        $payload = $this->operationItemPayload();
        $payload['quantity'] = ['units' => '10'];

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('field "quantity" must be an integer');

        $this->createMapper()->map(['items' => [$payload], 'hasNext' => false]);
    }

    public function testMapMissingRequiredMoneyThrows(): void
    {
        $payload = $this->operationItemPayload();
        unset($payload['payment']);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('field "payment" must be a money object');

        $this->createMapper()->map(['items' => [$payload], 'hasNext' => false]);
    }

    public function testMapMissingRequiredTradeFieldThrows(): void
    {
        $payload = $this->operationItemPayload();
        unset($payload['tradesInfo']['trades'][0]['num']);

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('field "num" must be a string, missing given');

        $this->createMapper()->map(['items' => [$payload], 'hasNext' => false]);
    }

    public function testMapTradeOfWrongTypeThrows(): void
    {
        $payload = $this->operationItemPayload();
        $payload['tradesInfo']['trades'][0] = 'broken';

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('"tradesInfo.trades" must be a JSON object');

        $this->createMapper()->map(['items' => [$payload], 'hasNext' => false]);
    }
}
