<?php

declare(strict_types=1);

namespace TInvest\Core\Service\Instruments\Dto;

use DateTimeImmutable;

final readonly class AssetReportViewDto
{
    public function __construct(
        public readonly string $ticker,
        public readonly ?DateTimeImmutable $reportDate,
        public readonly int $periodYear,
        public readonly int $periodNum,
        public readonly string $periodType,
    ) {
    }
}
