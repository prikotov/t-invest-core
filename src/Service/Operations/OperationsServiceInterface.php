<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Operations;

use DateTimeImmutable;
use Generator;
use TInvest\Skill\Service\Operations\Dto\OperationViewDto;
use TInvest\Skill\Service\Operations\Dto\PortfolioViewDto;

interface OperationsServiceInterface
{
    public function getOperations(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        ?string $state = null,
        ?string $figi = null
    ): Generator;

    public function getPortfolio(?string $ticker = null): PortfolioViewDto;
}
