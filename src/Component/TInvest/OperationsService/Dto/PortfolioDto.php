<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OperationsService\Dto;

use TInvest\Skill\Component\TInvest\Shared\Dto\MoneyDto;
use TInvest\Skill\Component\TInvest\Shared\Dto\PercentDto;

final readonly class PortfolioDto
{
    /**
     * @param MoneyDto|null $totalAmountShares
     * @param MoneyDto|null $totalAmountBonds
     * @param MoneyDto|null $totalAmountEtf
     * @param MoneyDto|null $totalAmountCurrencies
     * @param MoneyDto|null $totalAmountFutures
     * @param PercentDto $expectedYield
     * @param MoneyDto|null $totalAmountPortfolio
     * @param list<PortfolioPositionDto> $positions
     */
    public function __construct(
        public readonly ?MoneyDto $totalAmountShares,
        public readonly ?MoneyDto $totalAmountBonds,
        public readonly ?MoneyDto $totalAmountEtf,
        public readonly ?MoneyDto $totalAmountCurrencies,
        public readonly ?MoneyDto $totalAmountFutures,
        public readonly PercentDto $expectedYield,
        public readonly ?MoneyDto $totalAmountPortfolio,
        public readonly array $positions,
    ) {
    }
}
