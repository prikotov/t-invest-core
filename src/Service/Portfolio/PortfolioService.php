<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Portfolio;

use Override;
use TInvest\Skill\Component\TInvest\OperationsService\OperationsServiceComponentInterface;
use TInvest\Skill\Service\Portfolio\Dto\PortfolioPositionViewDto;

final class PortfolioService implements PortfolioServiceInterface
{
    public function __construct(
        private readonly OperationsServiceComponentInterface $operationsService,
    ) {
    }

    #[Override]
    public function getPositions(): iterable
    {
        $portfolio = $this->operationsService->getPortfolio();

        foreach ($portfolio->positions as $position) {
            yield new PortfolioPositionViewDto(
                $position->figi,
                $position->instrumentType,
                $position->quantity->value,
                (string)$position->currentPrice->value . ' ' . $position->currentPrice->currency,
                $position->expectedYield->value,
            );
        }
    }
}
