<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\Shared\Factory;

use TInvest\Core\Component\TInvest\Shared\Dto\QuotationDto;
use TInvest\Core\Component\TInvest\Shared\Helper\QuantityHelper;

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
