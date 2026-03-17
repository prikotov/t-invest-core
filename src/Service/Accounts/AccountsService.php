<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Accounts;

use Generator;
use Override;
use TInvest\Skill\Component\TInvest\UsersService\UsersServiceComponentInterface;
use TInvest\Skill\Service\Accounts\Dto\AccountViewDto;

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
