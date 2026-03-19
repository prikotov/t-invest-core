<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\OperationsService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Core\Component\TInvest\OperationsService\Enum\OperationStateEnum;
use TInvest\Core\Component\TInvest\OperationsService\Enum\OperationTypeEnum;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\OperationMapper;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;

final class OperationMapperTest extends TestCase
{
    private OperationMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new OperationMapper(new MoneyFactory());
    }

    public function testMapEmptyData(): void
    {
        $result = $this->mapper->map([]);

        $this->assertEmpty($result->operations);
    }

    public function testMapOperations(): void
    {
        $data = [
            'operations' => [
                [
                    'id' => 'operation-1',
                    'parentOperationId' => null,
                    'currency' => 'RUB',
                    'payment' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 0],
                    'price' => ['currency' => 'RUB', 'units' => '100', 'nano' => 0],
                    'state' => 'OPERATION_STATE_EXECUTED',
                    'quantity' => '10',
                    'quantityRest' => '0',
                    'figi' => 'BBG000000001',
                    'instrumentType' => 'share',
                    'date' => '2024-01-15T10:00:00Z',
                    'type' => 'Покупка акций',
                    'operationType' => 'OPERATION_TYPE_BUY',
                    'instrumentUid' => 'instrument-1',
                ],
            ],
        ];

        $result = $this->mapper->map($data);

        $this->assertCount(1, $result->operations);
        $operation = $result->operations[0];
        $this->assertSame('operation-1', $operation->id);
        $this->assertSame('RUB', $operation->currency);
        $this->assertSame(1000.0, $operation->payment?->value);
        $this->assertSame(OperationStateEnum::EXECUTED, $operation->state);
        $this->assertSame(10, $operation->quantity);
        $this->assertSame(OperationTypeEnum::BUY, $operation->operationType);
    }

    public function testMapMultipleOperations(): void
    {
        $data = [
            'operations' => [
                [
                    'id' => 'operation-1',
                    'currency' => 'RUB',
                    'payment' => ['currency' => 'RUB', 'units' => '1000', 'nano' => 0],
                    'state' => 'OPERATION_STATE_EXECUTED',
                    'quantity' => '10',
                    'operationType' => 'OPERATION_TYPE_BUY',
                ],
                [
                    'id' => 'operation-2',
                    'currency' => 'RUB',
                    'payment' => ['currency' => 'RUB', 'units' => '2000', 'nano' => 0],
                    'state' => 'OPERATION_STATE_EXECUTED',
                    'quantity' => '10',
                    'operationType' => 'OPERATION_TYPE_SELL',
                ],
            ],
        ];

        $result = $this->mapper->map($data);

        $this->assertCount(2, $result->operations);
        $this->assertSame(OperationTypeEnum::BUY, $result->operations[0]->operationType);
        $this->assertSame(OperationTypeEnum::SELL, $result->operations[1]->operationType);
    }
}
