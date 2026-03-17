<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\Shared\Factory;

use TInvest\Skill\Component\TInvest\Shared\Dto\MoneyDto;
use TInvest\Skill\Component\TInvest\Shared\Helper\QuantityHelper;

final class MoneyFactory
{
    /**
     * @param array<string, mixed>|null $data
     */
    public function create(?array $data): ?MoneyDto
    {
        if (is_null($data)) {
            return null;
        }

        if (
            !isset($data['currency'])
            && !isset($data['units'])
            && !isset($data['nano'])
        ) {
            return null;
        }

        if (
            !isset($data['currency'])
            && $data['units'] == 0
            && $data['nano'] == 0
        ) {
            return null;
        }

        $units = (string)($data['units'] ?? '');
        $nano = (string)($data['nano'] ?? '');

        return new MoneyDto(
            (string)$data['currency'],
            QuantityHelper::toFloat($units, $nano),
            (int)$units,
            (int)$nano
        );
    }
}
