<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\UsersService;

use Generator;
use TInvest\Skill\Component\TInvest\UsersService\Dto\AccountDto;

interface UsersServiceComponentInterface
{
    public function getAccounts(): Generator;
}
