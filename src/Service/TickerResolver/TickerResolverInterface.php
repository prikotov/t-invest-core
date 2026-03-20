<?php

declare(strict_types=1);

namespace TInvest\Core\Service\TickerResolver;

interface TickerResolverInterface
{
    public function isValidTicker(string $ticker): bool;

    public function isFigi(string $id): bool;

    public function resolveTickerToUid(string $ticker): ?string;

    public function resolveFigiToTicker(string $figi): ?string;

    /**
     * @param array<string> $tickers
     * @return array<string>
     */
    public function resolveTickersToUids(array $tickers): array;
}
