<?php

declare(strict_types=1);

namespace TInvest\Skill\Component\TInvest\OperationsService;

use TInvest\Skill\Component\TInvest\OperationsService\Dto\GetOperationsRequestDto;
use TInvest\Skill\Component\TInvest\OperationsService\Dto\GetOperationsResponseDto;
use TInvest\Skill\Component\TInvest\OperationsService\Dto\PortfolioDto;

interface OperationsServiceComponentInterface
{
    public function getPortfolio(): PortfolioDto;

    public function getOperations(GetOperationsRequestDto $request): GetOperationsResponseDto;
}
