<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\Shared\Helper;

use DateTimeImmutable;

final class DateTimeHelper
{
    public static function create(string $dateTime): ?DateTimeImmutable
    {
        $date = new DateTimeImmutable($dateTime);
        if ($date->format('Y-m-d') === '1970-01-01') {
            $date = null;
        }

        return $date;
    }
}
