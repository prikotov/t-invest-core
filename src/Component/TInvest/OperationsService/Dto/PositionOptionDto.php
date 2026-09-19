<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

final readonly class PositionOptionDto
{
    public function __construct(
        public readonly string $positionUid,
        public readonly string $instrumentUid,
        public readonly int $blocked,
        public readonly int $balance,
    ) {
    }
}
