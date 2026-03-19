<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Mapper;

use TInvest\Core\Component\TInvest\InstrumentsService\Request\GetDividendsRequestDto;

final class GetDividendsRequestMapper
{
    public function map(GetDividendsRequestDto $dividendsRequestDto): string
    {
        $result = json_encode(array_filter([
            'figi' => $dividendsRequestDto->figi,
            'from' => $dividendsRequestDto->from?->format('Y-m-d'),
            'to' => $dividendsRequestDto->to?->format('Y-m-d'),
        ], fn(mixed $value) => !is_null($value)));

        if ($result === false) {
            throw new \RuntimeException('Failed to encode JSON');
        }

        return $result;
    }
}
