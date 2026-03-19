<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OrdersService\Mapper;

use RuntimeException;
use TInvest\Core\Component\TInvest\OrdersService\Dto\PostOrderRequestDto;

final class PostOrderRequestMapper
{
    public function map(PostOrderRequestDto $dto): string
    {
        $data = [
            'quantity' => $dto->quantity,
            'direction' => $dto->direction->name,
            'accountId' => $dto->accountId,
            'orderType' => $dto->orderType->name,
            'orderId' => $dto->orderId,
            'instrumentId' => $dto->instrumentId,
        ];

        if ($dto->price) {
            $data['price'] = [
                'units' => $dto->price->units,
                'nano' => $dto->price->nano,
            ];
        }

        $result = json_encode($data);
        if ($result === false) {
            throw new RuntimeException('Failed to encode JSON');
        }

        return $result;
    }
}
