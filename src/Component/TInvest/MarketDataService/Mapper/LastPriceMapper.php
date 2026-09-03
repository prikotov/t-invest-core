<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService\Mapper;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetLastPricesResponseDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\LastPriceDto;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;
use UnexpectedValueException;

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
        if (!array_key_exists('lastPrices', $data)) {
            throw new UnexpectedValueException('GetLastPrices: field "lastPrices" is missing.');
        }

        $lastPricesData = $data['lastPrices'];

        if (!is_array($lastPricesData)) {
            throw new UnexpectedValueException(sprintf(
                'GetLastPrices: field "lastPrices" must be a list, %s given.',
                get_debug_type($lastPricesData),
            ));
        }

        if (!array_is_list($lastPricesData)) {
            throw new UnexpectedValueException('GetLastPrices: field "lastPrices" must be a list, object given.');
        }

        $lastPrices = [];
        foreach ($lastPricesData as $lastPrice) {
            if (!is_array($lastPrice)) {
                throw new UnexpectedValueException(sprintf(
                    'GetLastPrices: each item of "lastPrices" must be an object, %s given.',
                    get_debug_type($lastPrice),
                ));
            }

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
