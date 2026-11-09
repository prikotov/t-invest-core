<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Dto;

final readonly class GetOperationsByCursorResponseDto
{
    /**
     * @param array<OperationItemDto> $items
     */
    public function __construct(
        public readonly array $items,
        public readonly bool $hasNext,
        public readonly ?string $nextCursor,
    ) {
    }
}
