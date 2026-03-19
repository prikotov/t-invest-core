<?php

declare(strict_types=1);

namespace TInvest\Core\Service\Accounts\Dto;

final readonly class AccountViewDto
{
    public function __construct(
        public string $id,
        public string $type,
        public string $name,
        public string $status,
        public string $accessLevel,
    ) {
    }
}
