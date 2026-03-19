<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\InstrumentsService\Mapper;

use DateTimeImmutable;
use Exception;
use Generator;
use TInvest\Core\Component\TInvest\InstrumentsService\Dto\BondEventDto;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

final class GetBondEventsResponseMapper
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
        /** @var array{events: array<int, array<string, mixed>>} $data */
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        /** @var array<string, mixed> $item */
        foreach ($data['events'] ?? [] as $item) {
            /** @var string|null $eventDate */
            $eventDate = $item['eventDate'] ?? null;
            /** @var string|null $fixDate */
            $fixDate = $item['fixDate'] ?? null;
            /** @var string|null $payDate */
            $payDate = $item['payDate'] ?? null;
            /** @var string|null $couponStartDate */
            $couponStartDate = $item['couponStartDate'] ?? null;
            /** @var string|null $couponEndDate */
            $couponEndDate = $item['couponEndDate'] ?? null;
            /** @var array<string, mixed>|null $eventTotalVol */
            $eventTotalVol = $item['eventTotalVol'] ?? null;
            /** @var array<string, mixed>|null $payOneBond */
            $payOneBond = $item['payOneBond'] ?? null;
            /** @var array<string, mixed>|null $couponInterestRate */
            $couponInterestRate = $item['couponInterestRate'] ?? null;

            yield new BondEventDto(
                instrumentId: (string)$item['instrumentId'],
                eventNumber: (int)($item['eventNumber'] ?? 0),
                eventDate: $eventDate !== null ? new DateTimeImmutable($eventDate) : null,
                eventType: (string)($item['eventType'] ?? 'EVENT_TYPE_UNSPECIFIED'),
                eventTotalVol: $eventTotalVol !== null ? $this->quotationFactory->create($eventTotalVol) : null,
                fixDate: $fixDate !== null ? new DateTimeImmutable($fixDate) : null,
                payDate: $payDate !== null ? new DateTimeImmutable($payDate) : null,
                payOneBond: $payOneBond !== null ? $this->moneyFactory->create($payOneBond) : null,
                couponPeriod: isset($item['couponPeriod']) ? (int)$item['couponPeriod'] : null,
                couponInterestRate: $couponInterestRate !== null ? $this->quotationFactory->create($couponInterestRate) : null,
                couponStartDate: $couponStartDate !== null ? new DateTimeImmutable($couponStartDate) : null,
                couponEndDate: $couponEndDate !== null ? new DateTimeImmutable($couponEndDate) : null,
                note: isset($item['note']) ? (string)$item['note'] : null,
            );
        }
    }
}
