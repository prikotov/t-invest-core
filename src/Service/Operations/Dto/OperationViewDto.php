<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Operations\Dto;

use DateTimeImmutable;

final readonly class OperationViewDto
{
    public function __construct(
        public string $id,
        public ?DateTimeImmutable $date,
        public string $type,
        public string $state,
        public float $payment,
        public float $price,
        public int $quantity,
        public string $instrument,
    ) {
    }
}
