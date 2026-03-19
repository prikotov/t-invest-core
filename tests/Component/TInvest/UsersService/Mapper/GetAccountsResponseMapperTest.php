<?php

declare(strict_types=1);

namespace TInvest\Core\Tests\Component\TInvest\UsersService\Mapper;

use PHPUnit\Framework\TestCase;
use TInvest\Core\Component\TInvest\UsersService\Mapper\GetAccountsResponseMapper;

final class GetAccountsResponseMapperTest extends TestCase
{
    public function testMapReturnsAccountDtos(): void
    {
        $json = json_encode([
            'accounts' => [
                [
                    'id' => 'account-1',
                    'type' => 'TINKOFF',
                    'name' => 'Main Account',
                    'status' => 'OPEN',
                    'openedDate' => '2024-01-01T00:00:00Z',
                    'closedDate' => '1970-01-01T00:00:00Z',
                    'accessLevel' => 'FULL_ACCESS',
                ],
                [
                    'id' => 'account-2',
                    'type' => 'TINKOFF_IIS',
                    'name' => 'IIS Account',
                    'status' => 'OPEN',
                    'openedDate' => '2024-06-01T00:00:00Z',
                    'closedDate' => '1970-01-01T00:00:00Z',
                    'accessLevel' => 'FULL_ACCESS',
                ],
            ],
        ]);

        $mapper = new GetAccountsResponseMapper();
        $accounts = iterator_to_array($mapper->map($json));

        $this->assertCount(2, $accounts);

        $this->assertSame('account-1', $accounts[0]->id);
        $this->assertSame('TINKOFF', $accounts[0]->type);
        $this->assertSame('Main Account', $accounts[0]->name);
        $this->assertSame('OPEN', $accounts[0]->status);
        $this->assertSame('FULL_ACCESS', $accounts[0]->accessLevel);
        $this->assertNotNull($accounts[0]->openedDate);
        $this->assertNull($accounts[0]->closedDate);

        $this->assertSame('account-2', $accounts[1]->id);
        $this->assertSame('TINKOFF_IIS', $accounts[1]->type);
    }

    public function testMapReturnsEmptyGeneratorForNoAccounts(): void
    {
        $json = json_encode(['accounts' => []]);

        $mapper = new GetAccountsResponseMapper();
        $accounts = iterator_to_array($mapper->map($json));

        $this->assertCount(0, $accounts);
    }
}
