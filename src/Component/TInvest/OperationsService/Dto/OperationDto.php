<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\OperationsService\Enum\OperationStateEnum;
use TInvest\Core\Component\TInvest\OperationsService\Enum\OperationTypeEnum;
use TInvest\Core\Component\TInvest\Shared\Dto\MoneyDto;

final readonly class OperationDto
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $parentOperationId,
        public readonly string $currency,
        public readonly ?MoneyDto $payment,
        public readonly ?MoneyDto $price,
        public readonly ?OperationStateEnum $state,
        public readonly int $quantity,
        public readonly ?int $quantityRest,
        public readonly ?string $figi,
        public readonly ?string $instrumentType,
        public readonly ?DateTimeImmutable $date,
        public readonly ?string $type,
        public readonly ?OperationTypeEnum $operationType,
        public readonly ?string $instrumentUid,
    ) {
    }
}
