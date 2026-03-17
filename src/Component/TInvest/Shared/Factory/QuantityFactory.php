<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\Shared\Factory;

use TInvest\Skill\Component\TInvest\Shared\Dto\QuantityDto;
use TInvest\Skill\Component\TInvest\Shared\Helper\QuantityHelper;

final class QuantityFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): QuantityDto
    {
        $units = (string)($data['units'] ?? '');
        $nano = (string)($data['nano'] ?? '');

        return new QuantityDto(
            QuantityHelper::toFloat($units, $nano),
            (int)$units,
            (int)$nano,
        );
    }
}
