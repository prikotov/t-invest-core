<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\OperationsService\Enum\OperationStateEnum;

final readonly class GetOperationsRequestDto
{
    public function __construct(
        public readonly DateTimeImmutable $from,
        public readonly DateTimeImmutable $to,
        public readonly ?OperationStateEnum $state = null,
        public readonly ?string $figi = null,
    ) {
    }
}
