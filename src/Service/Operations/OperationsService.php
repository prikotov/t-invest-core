<?php

declare(strict_types=1);

namespace TInvest\Skill\Service\Operations;

use DateTimeImmutable;
use Generator;
use Override;
use TInvest\Skill\Component\TInvest\OperationsService\Dto\GetOperationsRequestDto;
use TInvest\Skill\Component\TInvest\OperationsService\Enum\OperationStateEnum;
use TInvest\Skill\Component\TInvest\OperationsService\OperationsServiceComponentInterface;
use TInvest\Skill\Service\Operations\Dto\OperationViewDto;
use TInvest\Skill\Service\Operations\Dto\PortfolioPositionViewDto;
use TInvest\Skill\Service\Operations\Dto\PortfolioViewDto;

final class OperationsService implements OperationsServiceInterface
{
    public function __construct(
        private readonly OperationsServiceComponentInterface $component,
    ) {
    }

    #[Override]
    public function getOperations(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        ?string $state = null,
        ?string $figi = null
    ): Generator {
        $stateEnum = $this->parseState($state);
        $request = new GetOperationsRequestDto($from, $to, $stateEnum, $figi);
        $response = $this->component->getOperations($request);

        foreach ($response->operations as $operation) {
            yield new OperationViewDto(
                id: $operation->id,
                date: $operation->date,
                type: $operation->operationType?->name ?? $operation->type ?? 'N/A',
                state: $operation->state?->name ?? 'N/A',
                payment: $operation->payment?->value ?? 0.0,
                price: $operation->price?->value ?? 0.0,
                quantity: $operation->quantity,
                instrument: $operation->figi ?? $operation->instrumentUid ?? 'N/A',
            );
        }
    }

    #[Override]
    public function getPortfolio(?string $ticker = null): PortfolioViewDto
    {
        $portfolio = $this->component->getPortfolio();

        $positions = $portfolio->positions;
        if ($ticker !== null) {
            $positions = array_filter($positions, fn($p) => $p->ticker === $ticker);
        }

        $positionViews = array_map(
            fn($p) => new PortfolioPositionViewDto(
                ticker: $p->ticker,
                instrumentType: $p->instrumentType,
                quantity: $p->quantity->value,
                avgPrice: $p->averagePositionPrice?->value ?? 0.0,
                currentPrice: $p->currentPrice->value,
                expectedYield: $p->expectedYield->value,
            ),
            array_values($positions)
        );

        return new PortfolioViewDto(
            totalAmount: $portfolio->totalAmountPortfolio?->value,
            currency: $portfolio->totalAmountPortfolio?->currency,
            expectedYield: $portfolio->expectedYield->value,
            positions: $positionViews,
        );
    }

    private function parseState(?string $state): ?OperationStateEnum
    {
        if ($state === null) {
            return null;
        }

        return match (strtolower($state)) {
            'executed' => OperationStateEnum::EXECUTED,
            'canceled' => OperationStateEnum::CANCELED,
            'progress' => OperationStateEnum::PROGRESS,
            default => null,
        };
    }
}
