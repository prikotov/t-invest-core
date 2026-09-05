<?php

declare(strict_types=1);

namespace TInvest\Core\Component\TInvest\OperationsService\Mapper;

use Iterator;
use TInvest\Core\Component\TInvest\OperationsService\Dto\PortfolioDto;
use TInvest\Core\Component\TInvest\OperationsService\Dto\PortfolioPositionDto;
use TInvest\Core\Component\TInvest\Shared\Factory\MoneyFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\PercentFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuantityFactory;
use TInvest\Core\Component\TInvest\Shared\Factory\QuotationFactory;
use UnexpectedValueException;

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
        if (!array_key_exists('positions', $data)) {
            throw new UnexpectedValueException('GetPortfolio: field "positions" is missing.');
        }

        $positionsData = $data['positions'];

        if (!is_array($positionsData)) {
            throw new UnexpectedValueException(sprintf(
                'GetPortfolio: field "positions" must be a list, %s given.',
                get_debug_type($positionsData),
            ));
        }

        if (!array_is_list($positionsData)) {
            throw new UnexpectedValueException('GetPortfolio: field "positions" must be a list, object given.');
        }

        /** @var array<string, mixed>|null $expectedYieldData */
        $expectedYieldData = $data['expectedYield'] ?? null;

        return new PortfolioDto(
            $this->moneyFactory->create($data['totalAmountShares'] ?? null),
            $this->moneyFactory->create($data['totalAmountBonds'] ?? null),
            $this->moneyFactory->create($data['totalAmountEtf'] ?? null),
            $this->moneyFactory->create($data['totalAmountCurrencies'] ?? null),
            $this->moneyFactory->create($data['totalAmountFutures'] ?? null),
            $expectedYieldData !== null
                ? $this->percentFactory->create($expectedYieldData)
                : $this->percentFactory->create(['units' => 0, 'nano' => 0]),
            $this->moneyFactory->create($data['totalAmountPortfolio'] ?? null),
            $this->mapPositions($positionsData),
        );
    }

    /**
     * Ленивый валидирующий итератор позиций: DTO каждой позиции строится в
     * момент итерации, а не заранее. Корректный пустой список `positions: []`
     * допустим и даёт пустой итератор; отсутствие поля, неверный тип списка
     * или не-объект на месте позиции — ошибка контракта ответа (исключение).
     *
     * @param list<mixed> $positionsData
     *
     * @return Iterator<PortfolioPositionDto>
     */
    private function mapPositions(array $positionsData): Iterator
    {
        foreach ($positionsData as $position) {
            if (!is_array($position)) {
                throw new UnexpectedValueException(sprintf(
                    'GetPortfolio: each item of "positions" must be an object, %s given.',
                    get_debug_type($position),
                ));
            }

            yield new PortfolioPositionDto(
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
    }
}
