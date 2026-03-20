<?php

declare(strict_types=1);

namespace TInvest\Core\Service\TickerResolver;

use Override;
use Symfony\Contracts\Cache\CacheInterface;
use TInvest\Core\Service\Instruments\InstrumentsServiceInterface;

final class TickerResolver implements TickerResolverInterface
{
    private const CACHE_PREFIX = 'tinvest_ticker_';
    private const TICKER_PATTERN = '/^[A-Z0-9-]{1,12}$/';

    public function __construct(
        private readonly InstrumentsServiceInterface $instrumentsService,
        private readonly CacheInterface $cache,
    ) {
    }

    #[Override]
    public function isValidTicker(string $ticker): bool
    {
        if (str_starts_with($ticker, 'BBG') || str_starts_with($ticker, 'TCS')) {
            return false;
        }

        return preg_match(self::TICKER_PATTERN, $ticker) === 1;
    }

    #[Override]
    public function resolve(string $ticker): ?string
    {
        $cacheKey = self::CACHE_PREFIX . strtolower($ticker);

        return $this->cache->get($cacheKey, function () use ($ticker): ?string {
            return $this->instrumentsService->getInstrumentUidByTicker($ticker);
        });
    }

    #[Override]
    public function resolveBatch(array $tickers): array
    {
        $result = [];

        foreach ($tickers as $ticker) {
            $resolved = $this->resolve($ticker);
            if ($resolved !== null) {
                $result[] = $resolved;
            }
        }

        return $result;
    }
}
