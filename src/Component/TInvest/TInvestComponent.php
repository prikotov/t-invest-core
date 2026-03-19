<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest;

use TInvest\Core\Component\TInvest\InstrumentsService\InstrumentsServiceComponentInterface;
use TInvest\Core\Component\TInvest\OperationsService\OperationsServiceComponentInterface;
use TInvest\Core\Component\TInvest\OrdersService\OrdersServiceComponentInterface;
use TInvest\Core\Component\TInvest\UsersService\UsersServiceComponentInterface;

final class TInvestComponent
{
    public function __construct(
        private readonly UsersServiceComponentInterface $usersService,
        private readonly OperationsServiceComponentInterface $operationsService,
        private readonly InstrumentsServiceComponentInterface $instrumentsService,
        private readonly OrdersServiceComponentInterface $ordersService,
    ) {
    }

    public function getUsersService(): UsersServiceComponentInterface
    {
        return $this->usersService;
    }

    public function getOperationsService(): OperationsServiceComponentInterface
    {
        return $this->operationsService;
    }

    public function getInstrumentsService(): InstrumentsServiceComponentInterface
    {
        return $this->instrumentsService;
    }

    public function getOrdersService(): OrdersServiceComponentInterface
    {
        return $this->ordersService;
    }
}
