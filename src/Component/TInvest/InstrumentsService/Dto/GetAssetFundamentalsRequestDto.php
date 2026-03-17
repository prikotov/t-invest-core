<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService\Dto;

final readonly class GetAssetFundamentalsRequestDto
{
    /**
     * @param list<string> $assets
     */
    public function __construct(
        public array $assets,
    ) {
    }
}
