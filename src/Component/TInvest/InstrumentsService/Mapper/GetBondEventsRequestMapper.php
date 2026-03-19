<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Mapper;

use TInvest\Core\Component\TInvest\InstrumentsService\Dto\GetBondEventsRequestDto;

final class GetBondEventsRequestMapper
{
    public function map(GetBondEventsRequestDto $dto): string
    {
        $result = json_encode(array_filter([
            'instrumentId' => $dto->instrumentId,
            'type' => $dto->eventType,
            'from' => $dto->from?->format('Y-m-d\TH:i:s.v\Z'),
            'to' => $dto->to?->format('Y-m-d\TH:i:s.v\Z'),
        ], fn(mixed $value) => !is_null($value)));

        if ($result === false) {
            throw new \RuntimeException('Failed to encode JSON');
        }

        return $result;
    }
}
