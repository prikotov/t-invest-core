<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\UsersService\Dto;

use DateTimeImmutable;

final readonly class AccountDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $type,
        public readonly string $name,
        public readonly string $status,
        public readonly DateTimeImmutable $openedDate,
        public readonly ?DateTimeImmutable $closedDate,
        public readonly string $accessLevel
    ) {
    }
}
