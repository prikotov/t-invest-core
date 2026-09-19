<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

use TInvest\Core\Component\TInvest\Shared\Dto\MoneyDto;

final readonly class GetPositionsResponseDto
{
    /**
     * @param array<int, MoneyDto> $money
     * @param array<int, MoneyDto>|null $blocked null, если поле отсутствует в ответе
     * @param array<int, PositionSecurityDto> $securities
     * @param array<int, PositionFutureDto> $futures
     * @param array<int, PositionOptionDto> $options
     */
    public function __construct(
        public readonly array $money,
        public readonly ?array $blocked,
        public readonly array $securities,
        public readonly bool $limitsLoadingInProgress,
        public readonly array $futures,
        public readonly array $options,
    ) {
    }
}
