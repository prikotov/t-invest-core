<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Mapper;

use TInvest\Core\Component\TInvest\InstrumentsService\Dto\GetAssetFundamentalsRequestDto;

final readonly class GetAssetFundamentalsRequestMapper
{
    public function map(GetAssetFundamentalsRequestDto $dto): string
    {
        return json_encode([
            'assets' => $dto->assets,
        ], JSON_THROW_ON_ERROR);
    }
}
