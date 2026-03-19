<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Mapper;

use TInvest\Core\Component\TInvest\OperationsService\Dto\PortfolioDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\PortfolioPositionDto;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\PercentFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuantityFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;

final class GetPortfolioResponseMapper
{
    public function __construct(
        private readonly MoneyFactory $moneyFactory,
        private readonly PercentFactory $percentFactory,
        private readonly QuantityFactory $quantityFactory,
        private readonly QuotationFactory $quotationFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function map(array $data): PortfolioDto
    {
        /** @var array<int, array<string, mixed>> $positionsData */
        $positionsData = $data['positions'] ?? [];

        $positions = [];
        foreach ($positionsData as $position) {
            $positions[] = new PortfolioPositionDto(
                $position['figi'],
                $position['instrumentType'],
                $this->quantityFactory->create($position['quantity']),
                $this->moneyFactory->create($position['averagePositionPrice']),
                $this->quotationFactory->create($position['expectedYield']),
                $this->moneyFactory->create($position['currentNkd'] ?? null),
                $this->moneyFactory->create($position['currentPrice']),
                $this->moneyFactory->create($position['averagePositionPricePt'] ?? null),
                $this->moneyFactory->create($position['averagePositionPriceFifo']),
                $this->quantityFactory->create($position['quantityLots']),
                $position['blocked'],
                $this->quotationFactory->create($position['blockedLots'] ?? null),
                $position['positionUid'],
                $position['instrumentUid'],
                $this->moneyFactory->create($position['varMargin']),
                $this->quotationFactory->create($position['expectedYieldFifo']),
                $position['ticker'],
            );
        }

        /** @var array<string, mixed>|null $expectedYieldData */
        $expectedYieldData = $data['expectedYield'] ?? null;

        return new PortfolioDto(
            $this->moneyFactory->create($data['totalAmountShares'] ?? null),
            $this->moneyFactory->create($data['totalAmountBonds'] ?? null),
            $this->moneyFactory->create($data['totalAmountEtf'] ?? null),
            $this->moneyFactory->create($data['totalAmountCurrencies'] ?? null),
            $this->moneyFactory->create($data['totalAmountFutures'] ?? null),
            $expectedYieldData !== null ? $this->percentFactory->create($expectedYieldData) : $this->percentFactory->create(['units' => 0, 'nano' => 0]),
            $this->moneyFactory->create($data['totalAmountPortfolio'] ?? null),
            $positions,
        );
    }
}
