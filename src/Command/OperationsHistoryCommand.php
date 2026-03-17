<?php

declare(strict_types=1);

namespace TInvest\Skill\Command;

use DateTimeImmutable;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Skill\Component\TInvest\OperationsService\Dto\GetOperationsRequestDto;
use TInvest\Skill\Component\TInvest\OperationsService\Enum\OperationStateEnum;
use TInvest\Skill\Component\TInvest\OperationsService\OperationsServiceComponentInterface;

#[AsCommand(
    name: 'operations:history',
    description: 'Get operations history',
)]
final class OperationsHistoryCommand extends Command
{
    public function __construct(
        private readonly OperationsServiceComponentInterface $operationsService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Start date (Y-m-d)', '-30 days')
            ->addOption('to', 't', InputOption::VALUE_OPTIONAL, 'End date (Y-m-d)', 'now')
            ->addOption('state', 's', InputOption::VALUE_OPTIONAL, 'Operation state (executed, canceled, progress)')
            ->addOption('figi', null, InputOption::VALUE_OPTIONAL, 'Filter by FIGI');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fromStr = $input->getOption('from');
        $toStr = $input->getOption('to');
        $stateStr = $input->getOption('state');
        $figi = $input->getOption('figi');

        $from = new DateTimeImmutable($fromStr);
        $to = new DateTimeImmutable($toStr);
        $state = $this->parseState($stateStr);

        $request = new GetOperationsRequestDto($from, $to, $state, $figi);
        $response = $this->operationsService->getOperations($request);

        if (empty($response->operations)) {
            $output->writeln('<comment>No operations found</comment>');
            return Command::SUCCESS;
        }

        $output->writeln(sprintf(
            '<info>Operations (%s to %s):</info>',
            $from->format('Y-m-d'),
            $to->format('Y-m-d')
        ));
        $output->writeln('');

        $output->writeln(sprintf(
            '<info>%-20s %-10s %-10s %12s %12s %10s %-30s</info>',
            'Date',
            'Type',
            'State',
            'Payment',
            'Price',
            'Qty',
            'Instrument'
        ));

        foreach ($response->operations as $operation) {
            $date = $operation->date?->format('Y-m-d H:i') ?? 'N/A';
            $type = $operation->operationType?->name ?? $operation->type ?? 'N/A';
            $stateStr = $operation->state?->name ?? 'N/A';
            $payment = $operation->payment?->value ?? 0.0;
            $price = $operation->price?->value ?? 0.0;
            $instrument = $operation->figi ?? $operation->instrumentUid ?? 'N/A';

            $output->writeln(sprintf(
                '%-20s %-10s %-10s %12.2f %12.2f %10d %-30s',
                $date,
                $type,
                $stateStr,
                $payment,
                $price,
                $operation->quantity,
                $instrument
            ));
        }

        $output->writeln('');
        $output->writeln(sprintf('<info>Total operations: %d</info>', count($response->operations)));

        return Command::SUCCESS;
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
