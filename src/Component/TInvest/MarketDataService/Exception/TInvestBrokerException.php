<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService\Exception;

use RuntimeException;

final class TInvestBrokerException extends RuntimeException
{
    public function __construct(
        public readonly int|string $brokerCode,
        public readonly string $description,
        public readonly string $brokerMessage,
    ) {
        parent::__construct(sprintf(
            'T-Invest broker error: code=%s, description=%s, message=%s.',
            (string)$brokerCode,
            $description,
            $brokerMessage,
        ));
    }
}
