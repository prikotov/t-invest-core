<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\InstrumentsService\Mapper;

use DateTimeImmutable;
use Exception;
use Generator;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\DividendDto;
use TInvest\Skill\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Skill\Component\TInvest\Shared\Factory\QuotationFactory;

final class GetDividendsResponseMapper
{
    public function __construct(
        private readonly MoneyFactory $moneyFactory,
        private readonly QuotationFactory $quotationFactory,
    ) {
    }

    /**
     * @throws Exception
     */
    public function map(string $json): Generator
    {
        /** @var array{dividends: array<int, array<string, mixed>>} $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        /** @var array<string, mixed> $item */
        foreach ($data['dividends'] as $item) {
            /** @var string|null $paymentDate */
            $paymentDate = $item['paymentDate'] ?? null;
            /** @var string|null $declaredDate */
            $declaredDate = $item['declaredDate'] ?? null;
            /** @var string|null $lastBuyDate */
            $lastBuyDate = $item['lastBuyDate'] ?? null;
            /** @var string $recordDate */
            $recordDate = $item['recordDate'];
            /** @var string $createdAt */
            $createdAt = $item['createdAt'];
            /** @var array<string, mixed>|null $yieldValue */
            $yieldValue = $item['yieldValue'] ?? null;

            yield new DividendDto(
                $this->moneyFactory->create($item['dividendNet']),
                $paymentDate !== null ? new DateTimeImmutable($paymentDate) : null,
                $declaredDate !== null ? new DateTimeImmutable($declaredDate) : null,
                $lastBuyDate !== null ? new DateTimeImmutable($lastBuyDate) : null,
                (string)$item['dividendType'],
                new DateTimeImmutable($recordDate),
                (string)$item['regularity'],
                $this->moneyFactory->create($item['closePrice']),
                $yieldValue !== null ? $this->quotationFactory->create($yieldValue) : null,
                new DateTimeImmutable($createdAt),
            );
        }
    }
}
