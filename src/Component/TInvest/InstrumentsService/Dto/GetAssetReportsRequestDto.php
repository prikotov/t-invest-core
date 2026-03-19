<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Dto;

use DateTimeImmutable;

final readonly class GetAssetReportsRequestDto
{
    public function __construct(
        public readonly string $instrumentId,
        public readonly ?DateTimeImmutable $from = null,
        public readonly ?DateTimeImmutable $to = null,
    ) {
    }
}
