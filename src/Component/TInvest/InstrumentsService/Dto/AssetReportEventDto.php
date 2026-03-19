<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Dto;

use DateTimeImmutable;

final readonly class AssetReportEventDto
{
    public function __construct(
        public readonly string $instrumentId,
        public readonly ?DateTimeImmutable $reportDate,
        public readonly int $periodYear,
        public readonly int $periodNum,
        public readonly string $periodType,
        public readonly DateTimeImmutable $createdAt,
    ) {
    }
}
