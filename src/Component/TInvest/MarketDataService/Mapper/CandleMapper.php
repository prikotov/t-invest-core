<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\MarketDataService\Mapper;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\CandleDto;
use TInvest\Core\Component\TInvest\MarketDataService\Dto\GetCandlesResponseDto;
use TInvest\Core\Component\TInvest\Shared\Dto\QuotationDto;
use TInvest\Core\Component\TInvest\Shared\Exception\TInvestResponseException;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

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
        $candlesData = $data['candles'] ?? [];

        if (!is_array($candlesData)) {
            throw new TInvestResponseException(sprintf(
                'GetCandles: field "candles" must be a JSON array, %s given.',
                get_debug_type($candlesData),
            ));
        }

        $candles = [];
        foreach ($candlesData as $candle) {
            if (!is_array($candle)) {
                throw new TInvestResponseException(sprintf(
                    'GetCandles: each candle must be a JSON object, %s given.',
                    get_debug_type($candle),
                ));
            }

            $candles[] = new CandleDto(
                $this->mapQuotation($candle, 'open'),
                $this->mapQuotation($candle, 'high'),
                $this->mapQuotation($candle, 'low'),
                $this->mapQuotation($candle, 'close'),
                $this->mapVolume($candle),
                $this->mapTime($candle),
                $this->mapIsComplete($candle),
                isset($candle['volumeBuy']) ? (int)$candle['volumeBuy'] : null,
                isset($candle['volumeSell']) ? (int)$candle['volumeSell'] : null,
            );
        }

        return new GetCandlesResponseDto($candles);
    }

    /**
     * @param array<string, mixed> $candle
     */
    private function mapQuotation(array $candle, string $field): QuotationDto
    {
        $raw = $candle[$field] ?? null;

        if (!is_array($raw)) {
            throw new TInvestResponseException(sprintf(
                'GetCandles: candle field "%s" must be a JSON object with units/nano, %s.',
                $field,
                $raw === null ? 'field is missing' : sprintf('%s given', get_debug_type($raw)),
            ));
        }

        return $this->quotationFactory->create($raw)
            ?? throw new TInvestResponseException(
                sprintf('GetCandles: candle field "%s" must contain units/nano.', $field),
            );
    }

    /**
     * @param array<string, mixed> $candle
     */
    private function mapVolume(array $candle): int
    {
        return (int)($candle['volume']
            ?? throw new TInvestResponseException('GetCandles: candle field "volume" is missing.'));
    }

    /**
     * @param array<string, mixed> $candle
     */
    private function mapTime(array $candle): DateTimeImmutable
    {
        return new DateTimeImmutable(
            $candle['time']
                ?? throw new TInvestResponseException('GetCandles: candle field "time" is missing.'),
        );
    }

    /**
     * @param array<string, mixed> $candle
     */
    private function mapIsComplete(array $candle): bool
    {
        return (bool)($candle['isComplete']
            ?? throw new TInvestResponseException('GetCandles: candle field "isComplete" is missing.'));
    }
}
