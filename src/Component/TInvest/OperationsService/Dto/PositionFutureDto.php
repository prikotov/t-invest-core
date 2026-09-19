<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

final readonly class PositionFutureDto
{
    public function __construct(
        public readonly string $figi,
        public readonly int $blocked,
        public readonly int $balance,
        public readonly string $positionUid,
        public readonly string $instrumentUid,
    ) {
    }
}
