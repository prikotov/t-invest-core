<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Portfolio;

use TInvest\Skill\Service\Portfolio\Dto\PortfolioPositionViewDto;

interface PortfolioServiceInterface
{
    /**
     * @return iterable<PortfolioPositionViewDto>
     */
    public function getPositions(): iterable;
}
