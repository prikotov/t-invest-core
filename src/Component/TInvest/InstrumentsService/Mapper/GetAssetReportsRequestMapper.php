<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Mapper;

use TInvest\Core\Component\TInvest\InstrumentsService\Dto\GetAssetReportsRequestDto;

final class GetAssetReportsRequestMapper
{
    public function map(GetAssetReportsRequestDto $dto): string
    {
        $result = json_encode(array_filter([
            'instrumentId' => $dto->instrumentId,
            'from' => $dto->from?->format('Y-m-d\TH:i:s.v\Z'),
            'to' => $dto->to?->format('Y-m-d\TH:i:s.v\Z'),
        ], fn(mixed $value) => !is_null($value)));

        if ($result === false) {
            throw new \RuntimeException('Failed to encode JSON');
        }

        return $result;
    }
}
