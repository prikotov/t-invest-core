<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\Shared\Factory;

use TInvest\Core\Component\TInvest\Shared\Dto\PercentDto;
use TInvest\Core\Component\TInvest\Shared\Helper\QuantityHelper;

final class PercentFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): PercentDto
    {
        $units = (string)($data['units'] ?? '');
        $nano = (string)($data['nano'] ?? '');

        return new PercentDto(
            QuantityHelper::toFloat($units, $nano),
            (int)$units,
            (int)$nano
        );
    }
}
