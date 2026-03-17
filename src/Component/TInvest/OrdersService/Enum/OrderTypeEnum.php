<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OrdersService\Enum;

enum OrderTypeEnum: int
{
    case ORDER_TYPE_UNSPECIFIED = 0;
    case ORDER_TYPE_LIMIT = 1;
    case ORDER_TYPE_MARKET = 2;
}
