<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OperationsService\Dto;

final class GetOperationsResponseDto
{
    /**
     * @param array<OperationDto> $operations
     */
    public function __construct(
        public readonly array $operations,
    ) {
    }
}
