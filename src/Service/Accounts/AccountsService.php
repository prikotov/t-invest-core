<?php

declare(strict_types=1);

namespace TInvest\Core\Service\Accounts;

use Generator;
use Override;
use TInvest\Core\Component\TInvest\UsersService\UsersServiceComponentInterface;
use TInvest\Core\Service\Accounts\Dto\AccountViewDto;

final class AccountsService implements AccountsServiceInterface
{
    public function __construct(
        private readonly UsersServiceComponentInterface $usersService,
    ) {
    }

    #[Override]
    public function getAccounts(): Generator
    {
        foreach ($this->usersService->getAccounts() as $account) {
            yield new AccountViewDto(
                $account->id,
                $account->type,
                $account->name,
                $account->status,
                $account->accessLevel,
            );
        }
    }
}
