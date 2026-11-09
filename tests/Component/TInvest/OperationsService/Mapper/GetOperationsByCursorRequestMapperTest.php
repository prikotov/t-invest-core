<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\OperationsService\Mapper;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use TInvest\Core\Component\TInvest\OperationsService\Dto\GetOperationsByCursorRequestDto;
use TInvest\Core\Component\TInvest\OperationsService\Enum\OperationStateEnum;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\GetOperationsByCursorRequestMapper;

final class GetOperationsByCursorRequestMapperTest extends TestCase
{
    public function testMapDefaults(): void
    {
        $mapper = new GetOperationsByCursorRequestMapper();

        $data = json_decode(
            $mapper->map(new GetOperationsByCursorRequestDto(accountId: 'account-1')),
            true,
        );

        $this->assertSame(['accountId' => 'account-1', 'limit' => 500], $data);
    }

    public function testMapNullLimitOmitsField(): void
    {
        $mapper = new GetOperationsByCursorRequestMapper();

        $data = json_decode(
            $mapper->map(new GetOperationsByCursorRequestDto(accountId: 'account-1', limit: null)),
            true,
        );

        $this->assertSame(['accountId' => 'account-1'], $data);
        $this->assertArrayNotHasKey('limit', $data);
        $this->assertArrayNotHasKey('cursor', $data);
    }

    public function testMapFullRequest(): void
    {
        $mapper = new GetOperationsByCursorRequestMapper();

        $data = json_decode($mapper->map(new GetOperationsByCursorRequestDto(
            accountId: 'account-1',
            cursor: 'cursor-2',
            limit: 100,
            instrumentId: 'BBG000000001',
            from: new DateTimeImmutable('2024-01-01T00:00:00+00:00'),
            to: new DateTimeImmutable('2024-02-01T00:00:00+00:00'),
            operationTypes: ['OPERATION_TYPE_BUY', 'OPERATION_TYPE_SELL'],
            state: OperationStateEnum::EXECUTED,
            withoutCommissions: false,
            withoutTrades: true,
            withoutOvernights: false,
        )), true);

        $this->assertSame('account-1', $data['accountId']);
        $this->assertSame('cursor-2', $data['cursor']);
        $this->assertSame(100, $data['limit']);
        $this->assertSame('BBG000000001', $data['instrumentId']);
        $this->assertSame('2024-01-01T00:00:00+00:00', $data['from']);
        $this->assertSame('2024-02-01T00:00:00+00:00', $data['to']);
        $this->assertSame(['OPERATION_TYPE_BUY', 'OPERATION_TYPE_SELL'], $data['operationTypes']);
        $this->assertSame('OPERATION_STATE_EXECUTED', $data['state']);
        $this->assertSame(false, $data['withoutCommissions']);
        $this->assertSame(true, $data['withoutTrades']);
        $this->assertSame(false, $data['withoutOvernights']);
    }
}
