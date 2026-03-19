<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OrdersService\Mapper;

use TInvest\Core\Component\TInvest\OrdersService\Dto\PostOrderResponseDto;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

final class PostOrderResponseMapper
{
    public function __construct(
        private readonly MoneyFactory $moneyFactory,
        private readonly QuotationFactory $quotationFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function map(array $data): PostOrderResponseDto
    {
        return new PostOrderResponseDto(
            $data['orderId'],
            $data['executionReportStatus'],
            (int)$data['lotsRequested'],
            (int)$data['lotsExecuted'],
            $this->moneyFactory->create($data['initialOrderPrice']),
            $this->moneyFactory->create($data['executedOrderPrice']),
            $this->moneyFactory->create($data['totalOrderAmount']),
            $this->moneyFactory->create($data['initialCommission']),
            $this->moneyFactory->create($data['executedCommission']),
            isset($data['aciValue']) ? $this->moneyFactory->create($data['aciValue']) : null,
            $data['figi'],
            $data['direction'],
            $this->moneyFactory->create($data['initialSecurityPrice']),
            $data['orderType'],
            $data['message'],
            $this->quotationFactory->create($data['initial_order_price_pt'] ?? null),
            $data['instrumentUid'],
        );
    }
}
