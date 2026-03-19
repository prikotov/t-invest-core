<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OrdersService\Dto;

use DateTimeImmutable;

final readonly class CancelOrderResponseDto
{
    public function __construct(
        public readonly DateTimeImmutable $time
    ) {
    }
}
