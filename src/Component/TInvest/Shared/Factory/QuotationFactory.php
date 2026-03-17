<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\Shared\Factory;

use TInvest\Skill\Component\TInvest\Shared\Dto\QuotationDto;
use TInvest\Skill\Component\TInvest\Shared\Helper\QuantityHelper;

final class QuotationFactory
{
    public function create(?array $data): ?QuotationDto
    {
        if (is_null($data)) {
            return null;
        }

        $units = (string)($data['units'] ?? '');
        $nano = (string)($data['nano'] ?? '');

        return new QuotationDto(
            QuantityHelper::toFloat($units, $nano),
            (int)$units,
            (int)$nano
        );
    }
}
