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
use TInvest\Core\Service\TickerResolver\TickerResolverInterface;

#[AsCommand(
    name: 'events:reports',
    description: 'Get asset report events calendar',
)]
final class ReportsCommand extends Command
{
    public function __construct(
        private readonly InstrumentsServiceInterface $instrumentsService,
        private readonly TickerResolverInterface $tickerResolver,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addOption('ticker', 't', InputOption::VALUE_REQUIRED, 'Instrument ticker')
            ->addOption('figi', null, InputOption::VALUE_REQUIRED, 'Instrument FIGI')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'From date (YYYY-MM-DD)')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'To date (YYYY-MM-DD)')
            ->addOption('order', 'o', InputOption::VALUE_OPTIONAL, 'Sort order (asc, desc)', 'desc')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit results', '0');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ticker = $input->getOption('ticker');
        $figi = $input->getOption('figi');

        $ticker = is_string($ticker) && $ticker !== '' ? $ticker : null;
        $figi = is_string($figi) && $figi !== '' ? $figi : null;

        if ($ticker === null && $figi === null) {
            $output->writeln('<error>--ticker or --figi is required</error>');
            return Command::FAILURE;
        }

        if ($ticker !== null && $figi !== null) {
            $resolvedTicker = $this->tickerResolver->resolveFigiToTicker($figi);
            if ($resolvedTicker === null) {
                $output->writeln(sprintf('<error>Cannot resolve FIGI %s</error>', $figi));
                return Command::FAILURE;
            }
            if ($resolvedTicker !== $ticker) {
                $output->writeln(sprintf(
                    '<error>FIGI %s resolves to ticker %s, not %s</error>',
                    $figi,
                    $resolvedTicker,
                    $ticker,
                ));
                return Command::FAILURE;
            }
        }

        if ($ticker === null && $figi !== null) {
            $ticker = $this->tickerResolver->resolveFigiToTicker($figi);
            if ($ticker === null) {
                $output->writeln(sprintf('<error>Cannot resolve FIGI %s to ticker</error>', $figi));
                return Command::FAILURE;
            }
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

        $order = $input->getOption('order');
        $limit = (int)$input->getOption('limit');

        if ($order === 'asc') {
            $reports = array_reverse($reports);
        }

        if ($limit > 0) {
            $reports = array_slice($reports, 0, $limit);
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
