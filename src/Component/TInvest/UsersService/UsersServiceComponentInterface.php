<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\UsersService;

use Generator;
use TInvest\Core\Component\TInvest\UsersService\Dto\AccountDto;

interface UsersServiceComponentInterface
{
    public function getAccounts(): Generator;
}
