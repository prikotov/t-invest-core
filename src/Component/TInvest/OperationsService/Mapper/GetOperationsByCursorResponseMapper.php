<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Mapper;

use DateTimeImmutable;
use UnexpectedValueException;
use TInvest\Core\Component\TInvest\OperationsService\Dto\GetOperationsByCursorResponseDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\OperationItemDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\TradeInfoDto;
use TInvest\Core\Component\TInvest\Shared\Dto\MoneyDto;
use TInvest\Core\Component\TInvest\Shared\Dto\QuotationDto;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

final class GetOperationsByCursorResponseMapper
{
    private const OPERATION = 'GetOperationsByCursor';

    public function __construct(
        private readonly MoneyFactory $moneyFactory,
        private readonly QuotationFactory $quotationFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function map(array $data): GetOperationsByCursorResponseDto
    {
        $hasNext = (bool)($data['hasNext'] ?? false);

        $items = [];
        if (isset($data['items'])) {
            if (!is_array($data['items'])) {
                throw new UnexpectedValueException(sprintf(
                    '%s: field "items" must be a JSON array, %s given.',
                    self::OPERATION,
                    get_debug_type($data['items']),
                ));
            }

            foreach ($data['items'] as $item) {
                if (!is_array($item)) {
                    throw new UnexpectedValueException(sprintf(
                        '%s: each item of "items" must be a JSON object, %s given.',
                        self::OPERATION,
                        get_debug_type($item),
                    ));
                }

                $items[] = $this->mapItem($item);
            }
        }

        return new GetOperationsByCursorResponseDto(
            $items,
            $hasNext,
            $this->mapNextCursor($data, $hasNext),
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    private function mapNextCursor(array $data, bool $hasNext): ?string
    {
        if (!$hasNext) {
            return null;
        }

        if (!isset($data['nextCursor'])) {
            return null;
        }

        if (!is_string($data['nextCursor'])) {
            throw new UnexpectedValueException(sprintf(
                '%s: field "nextCursor" must be a string, %s given.',
                self::OPERATION,
                get_debug_type($data['nextCursor']),
            ));
        }

        return $data['nextCursor'];
    }

    /**
     * @param array<string, mixed> $item
     */
    private function mapItem(array $item): OperationItemDto
    {
        return new OperationItemDto(
            $this->requireString($item, 'cursor'),
            $this->requireString($item, 'brokerAccountId'),
            $this->requireString($item, 'id'),
            $this->optionalString($item, 'parentOperationId'),
            $this->requireString($item, 'name'),
            new DateTimeImmutable($this->requireString($item, 'date')),
            $this->requireString($item, 'type'),
            $this->requireString($item, 'description'),
            $this->requireString($item, 'state'),
            $this->requireString($item, 'instrumentUid'),
            $this->requireString($item, 'figi'),
            $this->requireString($item, 'instrumentType'),
            $this->requireString($item, 'instrumentKind'),
            $this->requireMoney($item, 'payment'),
            $this->requireMoney($item, 'price'),
            $this->optionalMoney($item, 'commission'),
            $this->optionalMoney($item, 'yield'),
            $this->optionalQuotation($item, 'yieldRelative'),
            $this->optionalMoney($item, 'accruedInt'),
            $this->requireInt($item, 'quantity'),
            $this->requireInt($item, 'quantityRest'),
            $this->requireInt($item, 'quantityDone'),
            isset($item['cancelDateTime'])
                ? new DateTimeImmutable($this->requireString($item, 'cancelDateTime'))
                : null,
            $this->requireString($item, 'cancelReason'),
            $this->mapTrades($item),
            $this->requireString($item, 'assetUid'),
        );
    }

    /**
     * @param array<string, mixed> $item
     * @return array<TradeInfoDto>|null
     */
    private function mapTrades(array $item): ?array
    {
        $trades = [];
        foreach ($item['tradesInfo']['trades'] ?? [] as $trade) {
            if (!is_array($trade)) {
                throw new UnexpectedValueException(sprintf(
                    '%s: each item of "tradesInfo.trades" must be a JSON object, %s given.',
                    self::OPERATION,
                    get_debug_type($trade),
                ));
            }

            $trades[] = new TradeInfoDto(
                $this->requireString($trade, 'num'),
                new DateTimeImmutable($this->requireString($trade, 'date')),
                $this->requireInt($trade, 'quantity'),
                $this->requireMoney($trade, 'price'),
                $this->requireMoney($trade, 'yield'),
                $this->requireQuotation($trade, 'yieldRelative'),
            );
        }

        if ($trades === []) {
            return null;
        }

        return $trades;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function requireString(array $item, string $field): string
    {
        if (!isset($item[$field]) || !is_string($item[$field])) {
            throw new UnexpectedValueException(sprintf(
                '%s: field "%s" must be a string, %s given.',
                self::OPERATION,
                $field,
                isset($item[$field]) ? get_debug_type($item[$field]) : 'missing',
            ));
        }

        return $item[$field];
    }

    /**
     * @param array<string, mixed> $item
     */
    private function optionalString(array $item, string $field): ?string
    {
        if (!isset($item[$field])) {
            return null;
        }

        if (!is_string($item[$field])) {
            throw new UnexpectedValueException(sprintf(
                '%s: field "%s" must be a string, %s given.',
                self::OPERATION,
                $field,
                get_debug_type($item[$field]),
            ));
        }

        return $item[$field];
    }

    /**
     * @param array<string, mixed> $item
     */
    private function requireInt(array $item, string $field): int
    {
        if (!isset($item[$field]) || (!is_int($item[$field]) && !is_string($item[$field]))) {
            throw new UnexpectedValueException(sprintf(
                '%s: field "%s" must be an integer, %s given.',
                self::OPERATION,
                $field,
                isset($item[$field]) ? get_debug_type($item[$field]) : 'missing',
            ));
        }

        return (int)$item[$field];
    }

    /**
     * @param array<string, mixed> $item
     */
    private function requireMoney(array $item, string $field): MoneyDto
    {
        $money = $this->optionalMoney($item, $field);

        if ($money === null) {
            throw new UnexpectedValueException(sprintf(
                '%s: field "%s" must be a money object.',
                self::OPERATION,
                $field,
            ));
        }

        return $money;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function optionalMoney(array $item, string $field): ?MoneyDto
    {
        if (!isset($item[$field]) || !is_array($item[$field])) {
            return null;
        }

        return $this->moneyFactory->create($item[$field]);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function optionalQuotation(array $item, string $field): ?QuotationDto
    {
        if (!isset($item[$field]) || !is_array($item[$field])) {
            return null;
        }

        return $this->quotationFactory->create($item[$field]);
    }

    /**
     * @param array<string, mixed> $item
     */
    private function requireQuotation(array $item, string $field): QuotationDto
    {
        $quotation = $this->optionalQuotation($item, $field);

        if ($quotation === null) {
            throw new UnexpectedValueException(sprintf(
                '%s: field "%s" must be a quotation object.',
                self::OPERATION,
                $field,
            ));
        }

        return $quotation;
    }
}
