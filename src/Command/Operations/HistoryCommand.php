<?php

declare(strict_types=1);

namespace TInvest\Core\Command\Operations;

use DateTimeImmutable;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Core\Service\Operations\OperationsServiceInterface;

#[AsCommand(
    name: 'operations:history',
    description: 'Get operations history',
)]
final class HistoryCommand extends Command
{
    public function __construct(
        private readonly OperationsServiceInterface $operationsService,
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

        $operations = [];
        foreach ($this->operationsService->getOperations($from, $to, $stateStr, $figi) as $operation) {
            $operations[] = $operation;
        }

        if ($operations === []) {
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

        foreach ($operations as $operation) {
            $date = $operation->date?->format('Y-m-d H:i') ?? 'N/A';
            $output->writeln(sprintf(
                '%-20s %-10s %-10s %12.2f %12.2f %10d %-30s',
                $date,
                $operation->type,
                $operation->state,
                $operation->payment,
                $operation->price,
                $operation->quantity,
                $operation->instrument
            ));
        }

        $output->writeln('');
        $output->writeln(sprintf('<info>Total operations: %d</info>', count($operations)));

        return Command::SUCCESS;
    }
}
