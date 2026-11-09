<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Mapper;

use RuntimeException;
use TInvest\Core\Component\TInvest\OperationsService\Dto\GetOperationsByCursorRequestDto;

final class GetOperationsByCursorRequestMapper
{
    public function map(GetOperationsByCursorRequestDto $dto): string
    {
        $data = array_filter([
            'accountId' => $dto->accountId,
            'instrumentId' => $dto->instrumentId,
            'from' => $dto->from?->format('c'),
            'to' => $dto->to?->format('c'),
            'operationTypes' => $dto->operationTypes,
            'state' => $dto->state?->value,
            'withoutCommissions' => $dto->withoutCommissions,
            'withoutTrades' => $dto->withoutTrades,
            'withoutOvernights' => $dto->withoutOvernights,
            'cursor' => $dto->cursor,
            'limit' => $dto->limit,
        ], fn(mixed $value) => !is_null($value));

        $result = json_encode($data);
        if ($result === false) {
            throw new RuntimeException('Failed to encode JSON');
        }

        return $result;
    }
}
