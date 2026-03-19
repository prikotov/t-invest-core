<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

final readonly class GetOperationsResponseDto
{
    /**
     * @param array<OperationDto> $operations
     */
    public function __construct(
        public readonly array $operations,
    ) {
    }
}
