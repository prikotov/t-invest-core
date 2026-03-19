<?php

declare(strict_types=1);

namespace TInvest\Core\Service\Accounts;

use Generator;
use TInvest\Core\Service\Accounts\Dto\AccountViewDto;

interface AccountsServiceInterface
{
    public function getAccounts(): Generator;
}
