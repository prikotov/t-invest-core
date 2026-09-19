<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

final readonly class PositionSecurityDto
{
    public function __construct(
        public readonly string $figi,
        public readonly int $blocked,
        public readonly int $balance,
        public readonly string $positionUid,
        public readonly string $instrumentUid,
        public readonly bool $exchangeBlocked,
        public readonly string $instrumentType,
    ) {
    }
}
