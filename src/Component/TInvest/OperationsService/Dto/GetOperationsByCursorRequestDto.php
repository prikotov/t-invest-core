<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\OperationsService\Enum\OperationStateEnum;

final readonly class GetOperationsByCursorRequestDto
{
    /**
     * @param array<string>|null $operationTypes
     */
    public function __construct(
        public readonly string $accountId,
        public readonly ?string $cursor = null,
        public readonly ?int $limit = 500,
        public readonly ?string $instrumentId = null,
        public readonly ?DateTimeImmutable $from = null,
        public readonly ?DateTimeImmutable $to = null,
        public readonly ?array $operationTypes = null,
        public readonly ?OperationStateEnum $state = null,
        public readonly ?bool $withoutCommissions = null,
        public readonly ?bool $withoutTrades = null,
        public readonly ?bool $withoutOvernights = null,
    ) {
    }
}
