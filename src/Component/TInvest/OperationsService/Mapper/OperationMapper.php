<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Mapper;

use DateTimeImmutable;
use TInvest\Core\Component\TInvest\OperationsService\Dto\GetOperationsResponseDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\OperationDto;
use TInvest\Core\Component\TInvest\OperationsService\Enum\OperationStateEnum;
use TInvest\Core\Component\TInvest\OperationsService\Enum\OperationTypeEnum;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;

final class OperationMapper
{
    public function __construct(
        private readonly MoneyFactory $moneyFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function map(array $data): GetOperationsResponseDto
    {
        /** @var array<int, array<string, mixed>> $operationsData */
        $operationsData = $data['operations'] ?? [];

        $operations = [];
        foreach ($operationsData as $operation) {
            $operations[] = new OperationDto(
                $operation['id'] ?? '',
                $operation['parentOperationId'] ?? null,
                $operation['currency'] ?? '',
                $this->moneyFactory->create($operation['payment'] ?? null),
                $this->moneyFactory->create($operation['price'] ?? null),
                $this->mapState($operation['state'] ?? null),
                (int)($operation['quantity'] ?? 0),
                isset($operation['quantityRest']) ? (int)$operation['quantityRest'] : null,
                $operation['figi'] ?? null,
                $operation['instrumentType'] ?? null,
                isset($operation['date']) ? new DateTimeImmutable($operation['date']) : null,
                $operation['type'] ?? null,
                $this->mapType($operation['operationType'] ?? null),
                $operation['instrumentUid'] ?? null,
            );
        }

        return new GetOperationsResponseDto($operations);
    }

    private function mapState(?string $state): ?OperationStateEnum
    {
        if ($state === null) {
            return null;
        }

        return OperationStateEnum::tryFrom($state);
    }

    private function mapType(?string $type): ?OperationTypeEnum
    {
        if ($type === null) {
            return null;
        }

        return OperationTypeEnum::tryFrom($type);
    }
}
