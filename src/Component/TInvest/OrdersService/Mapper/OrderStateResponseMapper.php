<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OrdersService\Mapper;

use DateTimeImmutable;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\OrderStageDto;
use TInvest\Skill\Component\TInvest\OrdersService\Dto\OrderStateDto;
use TInvest\Skill\Component\TInvest\Shared\Factory\MoneyFactory;

final class OrderStateResponseMapper
{
    public function __construct(
        private readonly MoneyFactory $moneyFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function map(array $data): OrderStateDto
    {
        $stages = [];
        /** @var array<int, array<string, mixed>> $stageItems */
        $stageItems = $data['stages'] ?? [];
        foreach ($stageItems as $stageItem) {
            $stages[] = new OrderStageDto(
                $this->moneyFactory->create($stageItem['price']),
                (int)$stageItem['quantity'],
                $stageItem['tradeId'],
            );
        }

        return new OrderStateDto(
            $data['orderId'],
            $data['executionReportStatus'],
            (int)$data['lotsRequested'],
            (int)$data['lotsExecuted'],
            $this->moneyFactory->create($data['initialOrderPrice']),
            $this->moneyFactory->create($data['executedOrderPrice']),
            $this->moneyFactory->create($data['totalOrderAmount']),
            $this->moneyFactory->create($data['averagePositionPrice']),
            $this->moneyFactory->create($data['initialCommission']),
            $this->moneyFactory->create($data['executedCommission']),
            $data['figi'],
            $data['direction'],
            $this->moneyFactory->create($data['initialSecurityPrice']),
            $stages,
            $this->moneyFactory->create($data['serviceCommission']),
            $data['currency'],
            $data['orderType'],
            new DateTimeImmutable($data['orderDate']),
            $data['instrumentUid'],
            $data['orderRequestId'],
        );
    }
}
