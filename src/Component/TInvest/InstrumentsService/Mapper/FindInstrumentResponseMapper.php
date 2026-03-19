<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Mapper;

use TInvest\Core\Component\TInvest\InstrumentsService\Dto\FindInstrumentResponseDto;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\InstrumentShortDto;

final readonly class FindInstrumentResponseMapper
{
    public function map(string $json): FindInstrumentResponseDto
    {
        /** @var array<string, mixed> $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $instruments = [];
        foreach ($data['instruments'] ?? [] as $item) {
            $instruments[] = new InstrumentShortDto(
                ticker: (string)($item['ticker'] ?? ''),
                uid: (string)($item['uid'] ?? ''),
                instrumentType: (string)($item['instrumentType'] ?? ''),
            );
        }

        return new FindInstrumentResponseDto($instruments);
    }
}
