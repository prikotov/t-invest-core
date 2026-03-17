<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService\Mapper;

use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\FindInstrumentRequestDto;

final readonly class FindInstrumentRequestMapper
{
    public function map(FindInstrumentRequestDto $dto): string
    {
        return json_encode([
            'query' => $dto->query,
        ], JSON_THROW_ON_ERROR);
    }
}
