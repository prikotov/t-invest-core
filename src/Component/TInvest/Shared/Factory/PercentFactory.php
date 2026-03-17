<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\Shared\Factory;

use TInvest\Skill\Component\TInvest\Shared\Dto\PercentDto;
use TInvest\Skill\Component\TInvest\Shared\Helper\QuantityHelper;

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
