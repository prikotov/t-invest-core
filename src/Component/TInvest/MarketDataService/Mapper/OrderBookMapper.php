<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService\Mapper;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetOrderBookResponseDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\OrderDto;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

final class OrderBookMapper
{
    public function __construct(
        private readonly QuotationFactory $quotationFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function map(array $data): GetOrderBookResponseDto
    {
        /** @var array<int, array<string, mixed>> $bidsData */
        $bidsData = $data['bids'] ?? [];
        /** @var array<int, array<string, mixed>> $asksData */
        $asksData = $data['asks'] ?? [];

        $bids = [];
        foreach ($bidsData as $bid) {
            $quotation = $this->quotationFactory->create($bid['price']);
            if ($quotation !== null) {
                $bids[] = new OrderDto($quotation->value, (int)($bid['quantity'] ?? 0));
            }
        }

        $asks = [];
        foreach ($asksData as $ask) {
            $quotation = $this->quotationFactory->create($ask['price']);
            if ($quotation !== null) {
                $asks[] = new OrderDto($quotation->value, (int)($ask['quantity'] ?? 0));
            }
        }

        $limitUpQuotation = isset($data['limitUp']) ? $this->quotationFactory->create($data['limitUp']) : null;
        $limitDownQuotation = isset($data['limitDown']) ? $this->quotationFactory->create($data['limitDown']) : null;

        return new GetOrderBookResponseDto(
            figi: $data['figi'] ?? '',
            depth: (int)($data['depth'] ?? 20),
            bids: $bids,
            asks: $asks,
            time: isset($data['time']) ? new DateTimeImmutable($data['time']) : new DateTimeImmutable(),
            instrumentUid: $data['instrumentUid'] ?? '',
            limitUp: $limitUpQuotation?->value,
            limitDown: $limitDownQuotation?->value,
        );
    }
}
