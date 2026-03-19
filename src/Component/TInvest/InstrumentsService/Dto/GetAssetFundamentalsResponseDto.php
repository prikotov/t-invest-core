<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Dto;

final readonly class GetAssetFundamentalsResponseDto
{
    /**
     * @param list<AssetFundamentalDto> $fundamentals
     */
    public function __construct(
        public array $fundamentals,
    ) {
    }
}
