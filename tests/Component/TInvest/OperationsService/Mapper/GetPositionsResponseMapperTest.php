<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\OperationsService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Core\Component\TInvest\OperationsService\Mapper\GetPositionsResponseMapper;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use UnexpectedValueException;

final class GetPositionsResponseMapperTest extends TestCase
{
    private GetPositionsResponseMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new GetPositionsResponseMapper(new MoneyFactory());
    }

    public function testMapMoney(): void
    {
        $result = $this->mapper->map([
            'money' => [
                ['currency' => 'rub', 'units' => '1000', 'nano' => 500000000],
                ['currency' => 'usd', 'units' => '12', 'nano' => 340000000],
            ],
        ]);

        $this->assertCount(2, $result->money);
        $this->assertSame('rub', $result->money[0]->currency);
        $this->assertSame(1000.5, $result->money[0]->value);
        $this->assertSame('usd', $result->money[1]->currency);
        $this->assertSame(12.34, $result->money[1]->value);
    }

    public function testMapMissingMoneyFieldGivesEmptyList(): void
    {
        $result = $this->mapper->map([]);

        $this->assertSame([], $result->money);
    }

    public function testMapMoneyOfWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a list');

        $this->mapper->map(['money' => 'broken']);
    }

    public function testMapMoneyObjectInsteadOfListThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('must be a list');

        $this->mapper->map(['money' => ['rub' => ['currency' => 'rub', 'units' => '1', 'nano' => 0]]]);
    }

    public function testMapMoneyItemOfWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('each item');

        $this->mapper->map(['money' => ['broken']]);
    }

    public function testMapMoneyItemWithoutValidMoneyValueThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('valid money value');

        $this->mapper->map(['money' => [['units' => 0, 'nano' => 0]]]);
    }

    public function testMapMissingBlockedFieldGivesNull(): void
    {
        $result = $this->mapper->map([]);

        $this->assertNull($result->blocked);
    }

    public function testMapBlocked(): void
    {
        $result = $this->mapper->map([
            'blocked' => [
                ['currency' => 'rub', 'units' => '250', 'nano' => 750000000],
            ],
        ]);

        $this->assertNotNull($result->blocked);
        $this->assertCount(1, $result->blocked);
        $this->assertSame('rub', $result->blocked[0]->currency);
        $this->assertSame(250.75, $result->blocked[0]->value);
    }

    public function testMapBlockedEmptyListIsValid(): void
    {
        $result = $this->mapper->map(['blocked' => []]);

        $this->assertSame([], $result->blocked);
    }

    public function testMapBlockedOfWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('blocked');

        $this->mapper->map(['blocked' => 15]);
    }

    public function testMapSecurities(): void
    {
        $result = $this->mapper->map([
            'securities' => [
                [
                    'figi' => 'BBG000000001',
                    'blocked' => 5,
                    'balance' => 100,
                    'positionUid' => 'pos-1',
                    'instrumentUid' => 'inst-1',
                    'exchangeBlocked' => true,
                    'instrumentType' => 'share',
                ],
            ],
        ]);

        $this->assertCount(1, $result->securities);
        $security = $result->securities[0];
        $this->assertSame('BBG000000001', $security->figi);
        $this->assertSame(5, $security->blocked);
        $this->assertSame(100, $security->balance);
        $this->assertSame('pos-1', $security->positionUid);
        $this->assertSame('inst-1', $security->instrumentUid);
        $this->assertTrue($security->exchangeBlocked);
        $this->assertSame('share', $security->instrumentType);
    }

    public function testMapSecurityOptionalFieldsFallBackToContractDefaults(): void
    {
        // proto3 JSON не сериализует поля с нулевыми значениями:
        // blocked и exchangeBlocked могут отсутствовать.
        $result = $this->mapper->map([
            'securities' => [
                [
                    'figi' => 'BBG000000001',
                    'balance' => 100,
                    'positionUid' => 'pos-1',
                    'instrumentUid' => 'inst-1',
                    'instrumentType' => 'share',
                ],
            ],
        ]);

        $this->assertSame(0, $result->securities[0]->blocked);
        $this->assertFalse($result->securities[0]->exchangeBlocked);
    }

    public function testMapSecuritiesItemOfWrongTypeThrows(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('each item');

        $this->mapper->map(['securities' => ['broken']]);
    }

    public function testMapFutures(): void
    {
        $result = $this->mapper->map([
            'futures' => [
                [
                    'figi' => 'FUT000000001',
                    'blocked' => 1,
                    'balance' => 3,
                    'positionUid' => 'fpos-1',
                    'instrumentUid' => 'finst-1',
                ],
            ],
        ]);

        $this->assertCount(1, $result->futures);
        $future = $result->futures[0];
        $this->assertSame('FUT000000001', $future->figi);
        $this->assertSame(1, $future->blocked);
        $this->assertSame(3, $future->balance);
        $this->assertSame('fpos-1', $future->positionUid);
        $this->assertSame('finst-1', $future->instrumentUid);
    }

    public function testMapOptions(): void
    {
        $result = $this->mapper->map([
            'options' => [
                [
                    'positionUid' => 'opos-1',
                    'instrumentUid' => 'oinst-1',
                    'blocked' => 2,
                    'balance' => 7,
                ],
            ],
        ]);

        $this->assertCount(1, $result->options);
        $option = $result->options[0];
        $this->assertSame('opos-1', $option->positionUid);
        $this->assertSame('oinst-1', $option->instrumentUid);
        $this->assertSame(2, $option->blocked);
        $this->assertSame(7, $option->balance);
    }

    /**
     * Регрессия маппинга эталона: элементы "options" попадают в "options",
     * а не в список фьючерсов.
     */
    public function testMapOptionsDoNotLeakIntoFutures(): void
    {
        $result = $this->mapper->map([
            'futures' => [$this->futureData('FUT000000001')],
            'options' => [
                [
                    'positionUid' => 'opos-1',
                    'instrumentUid' => 'oinst-1',
                    'blocked' => 2,
                    'balance' => 7,
                ],
            ],
        ]);

        $this->assertCount(1, $result->futures);
        $this->assertSame('FUT000000001', $result->futures[0]->figi);
        $this->assertCount(1, $result->options);
        $this->assertSame('opos-1', $result->options[0]->positionUid);
    }

    public function testMapLimitsLoadingInProgressDefaultsToFalse(): void
    {
        $result = $this->mapper->map([]);

        $this->assertFalse($result->limitsLoadingInProgress);
    }

    public function testMapLimitsLoadingInProgress(): void
    {
        $result = $this->mapper->map(['limitsLoadingInProgress' => true]);

        $this->assertTrue($result->limitsLoadingInProgress);
    }

    public function testMapFullPayload(): void
    {
        $result = $this->mapper->map([
            'money' => [
                ['currency' => 'rub', 'units' => '1000', 'nano' => 500000000],
            ],
            'blocked' => [
                ['currency' => 'rub', 'units' => '250', 'nano' => 0],
            ],
            'securities' => [
                [
                    'figi' => 'BBG000000001',
                    'blocked' => 5,
                    'balance' => 100,
                    'positionUid' => 'pos-1',
                    'instrumentUid' => 'inst-1',
                    'exchangeBlocked' => false,
                    'instrumentType' => 'share',
                ],
            ],
            'limitsLoadingInProgress' => true,
            'futures' => [$this->futureData('FUT000000001')],
            'options' => [
                [
                    'positionUid' => 'opos-1',
                    'instrumentUid' => 'oinst-1',
                    'blocked' => 2,
                    'balance' => 7,
                ],
            ],
        ]);

        $this->assertCount(1, $result->money);
        $this->assertSame(1000.5, $result->money[0]->value);
        $this->assertCount(1, $result->blocked);
        $this->assertSame(250.0, $result->blocked[0]->value);
        $this->assertCount(1, $result->securities);
        $this->assertTrue($result->limitsLoadingInProgress);
        $this->assertCount(1, $result->futures);
        $this->assertCount(1, $result->options);
    }

    /**
     * @return array<string, mixed>
     */
    private function futureData(string $figi): array
    {
        return [
            'figi' => $figi,
            'blocked' => 1,
            'balance' => 3,
            'positionUid' => 'fpos-' . $figi,
            'instrumentUid' => 'finst-' . $figi,
        ];
    }
}
