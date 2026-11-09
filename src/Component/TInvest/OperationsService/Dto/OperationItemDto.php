<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\Shared\Dto\MoneyDto;
use TInvest\Core\Component\TInvest\Shared\Dto\QuotationDto;

final readonly class OperationItemDto
{
    /**
     * @param array<TradeInfoDto>|null $trades
     */
    public function __construct(
        public readonly string $cursor,
        public readonly string $brokerAccountId,
        public readonly string $id,
        public readonly ?string $parentOperationId,
        public readonly string $name,
        public readonly DateTimeImmutable $date,
        public readonly string $type,
        public readonly string $description,
        public readonly string $state,
        public readonly string $instrumentUid,
        public readonly string $figi,
        public readonly string $instrumentType,
        public readonly string $instrumentKind,
        public readonly MoneyDto $payment,
        public readonly MoneyDto $price,
        public readonly ?MoneyDto $commission,
        public readonly ?MoneyDto $yield,
        public readonly ?QuotationDto $yieldRelative,
        public readonly ?MoneyDto $accruedInt,
        public readonly int $quantity,
        public readonly int $quantityRest,
        public readonly int $quantityDone,
        public readonly ?DateTimeImmutable $cancelDateTime,
        public readonly string $cancelReason,
        public readonly ?array $trades,
        public readonly string $assetUid,
    ) {
    }
}
