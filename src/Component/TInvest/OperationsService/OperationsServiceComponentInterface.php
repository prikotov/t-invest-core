<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService;

use TInvest\Core\Component\TInvest\OperationsService\Dto\GetOperationsByCursorRequestDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\GetOperationsByCursorResponseDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\GetOperationsRequestDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\GetOperationsResponseDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\GetPositionsResponseDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\PortfolioDto;

interface OperationsServiceComponentInterface
{
    public function getPortfolio(): PortfolioDto;

    public function getPositions(): GetPositionsResponseDto;

    public function getOperations(GetOperationsRequestDto $request): GetOperationsResponseDto;

    public function getOperationsByCursor(GetOperationsByCursorRequestDto $request): GetOperationsByCursorResponseDto;
}
