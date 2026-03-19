<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\UsersService\Mapper;

use Generator;
use TInvest\Core\Component\TInvest\Shared\Helper\DateTimeHelper;
use TInvest\Core\Component\TInvest\UsersService\Dto\AccountDto;

final class GetAccountsResponseMapper
{
    public function map(string $json): Generator
    {
        /** @var array{accounts: array<int, array<string, mixed>>} $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        foreach ($data['accounts'] as $account) {
            yield new AccountDto(
                $account['id'],
                $account['type'],
                $account['name'],
                $account['status'],
                DateTimeHelper::create($account['openedDate']),
                DateTimeHelper::create($account['closedDate']),
                $account['accessLevel']
            );
        }
    }
}
