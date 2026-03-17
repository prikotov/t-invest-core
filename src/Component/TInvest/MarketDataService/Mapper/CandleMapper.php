<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\MarketDataService\Mapper;

use DateTimeImmutable;
use TInvest\Skill\Component\TInvest\MarketDataService\Dto\CandleDto;
use TInvest\Skill\Component\TInvest\MarketDataService\Dto\GetCandlesResponseDto;
use TInvest\Skill\Component\TInvest\Shared\Factory\QuotationFactory;

final class CandleMapper
{
    public function __construct(
        private readonly QuotationFactory $quotationFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function map(array $data): GetCandlesResponseDto
    {
        /** @var array<int, array<string, mixed>> $candlesData */
        $candlesData = $data['candles'] ?? [];

        $candles = [];
        foreach ($candlesData as $candle) {
            $open = $this->quotationFactory->create($candle['open'])
                ?? throw new \InvalidArgumentException('Open price required');
            $high = $this->quotationFactory->create($candle['high'])
                ?? throw new \InvalidArgumentException('High price required');
            $low = $this->quotationFactory->create($candle['low'])
                ?? throw new \InvalidArgumentException('Low price required');
            $close = $this->quotationFactory->create($candle['close'])
                ?? throw new \InvalidArgumentException('Close price required');

            $candles[] = new CandleDto(
                $open,
                $high,
                $low,
                $close,
                (int)($candle['volume'] ?? 0),
                isset($candle['time']) ? new DateTimeImmutable($candle['time']) : new DateTimeImmutable(),
                $candle['isComplete'] ?? true,
                isset($candle['volumeBuy']) ? (int)$candle['volumeBuy'] : null,
                isset($candle['volumeSell']) ? (int)$candle['volumeSell'] : null,
            );
        }

        return new GetCandlesResponseDto($candles);
    }
}
