<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Accounts;

use Generator;
use TInvest\Skill\Service\Accounts\Dto\AccountViewDto;

interface AccountsServiceInterface
{
    public function getAccounts(): Generator;
}
