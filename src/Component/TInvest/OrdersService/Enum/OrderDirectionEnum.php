<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OrdersService\Enum;

enum OrderDirectionEnum: int
{
    case ORDER_DIRECTION_UNSPECIFIED = 0;
    case ORDER_DIRECTION_BUY = 1;
    case ORDER_DIRECTION_SELL = 2;
}
