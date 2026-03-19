<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\MarketDataService\Dto;

use DateTimeImmutable;
use TInvest\Skill\Component\TInvest\MarketDataService\Enum\CandleIntervalEnum;

final readonly class GetCandlesRequestDto
{
    public function __construct(
        public readonly string $instrumentId,
        public readonly DateTimeImmutable $from,
        public readonly DateTimeImmutable $to,
        public readonly CandleIntervalEnum $interval,
        public readonly ?int $limit = null,
    ) {
    }
}
