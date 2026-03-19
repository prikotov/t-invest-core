<?php

declare(strict_types=1);

namespace TInvest\Core\Command\Events;

use DateTimeImmutable;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Core\Service\Instruments\InstrumentsServiceInterface;

#[AsCommand(
    name: 'events:reports',
    description: 'Get asset report events calendar',
)]
final class ReportsCommand extends Command
{
    public function __construct(
        private readonly InstrumentsServiceInterface $instrumentsService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addOption('ticker', 't', InputOption::VALUE_REQUIRED, 'Instrument ticker')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'From date (YYYY-MM-DD)')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'To date (YYYY-MM-DD)');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ticker = $input->getOption('ticker');
        if (!is_string($ticker) || $ticker === '') {
            $output->writeln('<error>--ticker is required</error>');
            return Command::FAILURE;
        }

        $from = $input->getOption('from');
        $to = $input->getOption('to');

        $fromDate = is_string($from) ? new DateTimeImmutable($from) : null;
        $toDate = is_string($to) ? new DateTimeImmutable($to) : null;

        $reports = $this->instrumentsService->getAssetReports($ticker, $fromDate, $toDate);

        if ($reports === []) {
            $output->writeln(sprintf('<comment>No reports found for %s</comment>', $ticker));
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<info>Report calendar for %s</info>', $ticker));
        $output->writeln('');

        foreach ($reports as $report) {
            $periodType = match ($report->periodNum) {
                1 => 'Q1',
                2 => 'Q2',
                3 => 'Q3',
                4 => 'Q4',
                default => $report->periodType,
            };

            $output->writeln(sprintf(
                '<info>%s</info> | %s %d',
                $report->reportDate?->format('Y-m-d') ?? 'TBD',
                $periodType,
                $report->periodYear,
            ));
        }

        return Command::SUCCESS;
    }
}
