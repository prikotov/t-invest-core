<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Mapper;

use DateTimeImmutable;
use Exception;
use Generator;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\AssetReportEventDto;

final class GetAssetReportsResponseMapper
{
    /**
     * @throws Exception
     */
    public function map(string $json): Generator
    {
        /** @var array{events?: array<int, array<string, mixed>>} $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        /** @var array<string, mixed> $item */
        foreach ($data['events'] ?? [] as $item) {
            /** @var string|null $reportDate */
            $reportDate = $item['reportDate'] ?? null;
            /** @var string $createdAt */
            $createdAt = $item['createdAt'] ?? 'now';

            yield new AssetReportEventDto(
                instrumentId: (string)$item['instrumentId'],
                reportDate: $reportDate !== null ? new DateTimeImmutable($reportDate) : null,
                periodYear: (int)($item['periodYear'] ?? 0),
                periodNum: (int)($item['periodNum'] ?? 0),
                periodType: (string)($item['periodType'] ?? 'UNKNOWN'),
                createdAt: new DateTimeImmutable($createdAt),
            );
        }
    }
}
