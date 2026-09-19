<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Mapper;

use TInvest\Core\Component\TInvest\OperationsService\Dto\GetPositionsResponseDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\PositionFutureDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\PositionOptionDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\PositionSecurityDto;
use TInvest\Core\Component\TInvest\Shared\Dto\MoneyDto;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use UnexpectedValueException;

final class GetPositionsResponseMapper
{
    public function __construct(
        private readonly MoneyFactory $moneyFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function map(array $data): GetPositionsResponseDto
    {
        return new GetPositionsResponseDto(
            $this->mapMoney($data),
            $this->mapBlocked($data),
            $this->mapSecurities($data),
            (bool)($data['limitsLoadingInProgress'] ?? false),
            $this->mapFutures($data),
            $this->mapOptions($data),
        );
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, MoneyDto>
     */
    private function mapMoney(array $data): array
    {
        $money = [];
        foreach ($this->listField($data, 'money') as $moneyItem) {
            $money[] = $this->money($moneyItem, 'money');
        }

        return $money;
    }

    /**
     * Поле "blocked" опционально: отсутствие поля в ответе даёт null,
     * пустой список — пустой список.
     *
     * @param array<string, mixed> $data
     *
     * @return array<int, MoneyDto>|null
     */
    private function mapBlocked(array $data): ?array
    {
        if (!array_key_exists('blocked', $data)) {
            return null;
        }

        $blocked = [];
        foreach ($this->listField($data, 'blocked') as $blockedItem) {
            $blocked[] = $this->money($blockedItem, 'blocked');
        }

        return $blocked;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, PositionSecurityDto>
     */
    private function mapSecurities(array $data): array
    {
        $securities = [];
        foreach ($this->listField($data, 'securities') as $securityItem) {
            $security = $this->item($securityItem, 'securities');

            $securities[] = new PositionSecurityDto(
                $security['figi'],
                (int)($security['blocked'] ?? 0),
                (int)$security['balance'],
                $security['positionUid'],
                $security['instrumentUid'],
                $security['exchangeBlocked'] ?? false,
                $security['instrumentType'],
            );
        }

        return $securities;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, PositionFutureDto>
     */
    private function mapFutures(array $data): array
    {
        $futures = [];
        foreach ($this->listField($data, 'futures') as $futureItem) {
            $future = $this->item($futureItem, 'futures');

            $futures[] = new PositionFutureDto(
                $future['figi'],
                (int)$future['blocked'],
                (int)$future['balance'],
                $future['positionUid'],
                $future['instrumentUid'],
            );
        }

        return $futures;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<int, PositionOptionDto>
     */
    private function mapOptions(array $data): array
    {
        $options = [];
        foreach ($this->listField($data, 'options') as $optionItem) {
            $option = $this->item($optionItem, 'options');

            $options[] = new PositionOptionDto(
                $option['positionUid'],
                $option['instrumentUid'],
                (int)$option['blocked'],
                (int)$option['balance'],
            );
        }

        return $options;
    }

    /**
     * @param mixed $item
     */
    private function money(mixed $item, string $field): MoneyDto
    {
        $money = $this->moneyFactory->create($this->item($item, $field));

        if ($money === null) {
            throw new UnexpectedValueException(sprintf(
                'GetPositions: each item of "%s" must be a valid money value.',
                $field,
            ));
        }

        return $money;
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return list<mixed>
     */
    private function listField(array $data, string $field): array
    {
        $value = $data[$field] ?? [];

        if (!is_array($value)) {
            throw new UnexpectedValueException(sprintf(
                'GetPositions: field "%s" must be a list, %s given.',
                $field,
                get_debug_type($value),
            ));
        }

        if (!array_is_list($value)) {
            throw new UnexpectedValueException(sprintf(
                'GetPositions: field "%s" must be a list, object given.',
                $field,
            ));
        }

        return $value;
    }

    /**
     * @param mixed $item
     *
     * @return array<string, mixed>
     */
    private function item(mixed $item, string $field): array
    {
        if (!is_array($item)) {
            throw new UnexpectedValueException(sprintf(
                'GetPositions: each item of "%s" must be an object, %s given.',
                $field,
                get_debug_type($item),
            ));
        }

        return $item;
    }
}
