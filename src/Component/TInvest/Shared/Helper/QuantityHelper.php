<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\Shared\Helper;

final class QuantityHelper
{
    public static function toFloat(string $units, string $nano): float
    {
        return round((float)(int)$units + ((float)(int)$nano / 1000000000.0), 9);
    }

    public static function formatNano(string $nano): string
    {
        return str_pad($nano, 9, '0', STR_PAD_LEFT);
    }

    public static function createNano(string $fractionalPart): string
    {
        return str_pad(
            substr($fractionalPart, 0, 9),
            9,
            '0',
            STR_PAD_RIGHT
        );
    }
}
