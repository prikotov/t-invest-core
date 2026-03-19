<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService\Mapper;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetLastPricesResponseDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\LastPriceDto;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

final class LastPriceMapper
{
    public function __construct(
        private readonly QuotationFactory $quotationFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function map(array $data): GetLastPricesResponseDto
    {
        /** @var array<int, array<string, mixed>> $lastPricesData */
        $lastPricesData = $data['lastPrices'] ?? [];

        $lastPrices = [];
        foreach ($lastPricesData as $lastPrice) {
            $price = $this->quotationFactory->create($lastPrice['price'])
                ?? throw new \InvalidArgumentException('Price required');

            $lastPrices[] = new LastPriceDto(
                $lastPrice['figi'] ?? '',
                $price,
                isset($lastPrice['time']) ? new DateTimeImmutable($lastPrice['time']) : null,
                $lastPrice['ticker'] ?? null,
                $lastPrice['classCode'] ?? null,
                $lastPrice['instrumentUid'] ?? '',
            );
        }

        return new GetLastPricesResponseDto($lastPrices);
    }
}
