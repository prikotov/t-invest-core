<?php

declare(strict_types=1);

namespace TInvest\Core\Service\TickerResolver;

interface TickerResolverInterface
{
    public function isValidTicker(string $ticker): bool;

    public function resolve(string $ticker): ?string;

    /**
     * @param array<string> $tickers
     * @return array<string>
     */
    public function resolveBatch(array $tickers): array;
}
