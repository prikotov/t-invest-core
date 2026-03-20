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
    name: 'events:dividends',
    description: 'Get dividend events for an instrument',
)]
final class DividendsCommand extends Command
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
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'To date (YYYY-MM-DD)');
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

        $dividends = $this->instrumentsService->getDividends($ticker, $fromDate, $toDate);

        if ($dividends === []) {
            $output->writeln(sprintf('<comment>No dividends found for %s</comment>', $ticker));
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<info>Dividends for %s</info>', $ticker));
        $output->writeln('');

        foreach ($dividends as $dividend) {
            $output->writeln(sprintf(
                '<info>%s</info> | Net: %s %s | Yield: %s%%',
                $dividend->recordDate?->format('Y-m-d') ?? 'N/A',
                $dividend->dividendNet !== null ? number_format($dividend->dividendNet, 2) : 'N/A',
                $dividend->currency ?? 'N/A',
                $dividend->yieldValue !== null ? number_format($dividend->yieldValue, 2) : 'N/A',
            ));

            if ($dividend->lastBuyDate !== null) {
                $output->writeln(sprintf('  Last buy date: %s', $dividend->lastBuyDate->format('Y-m-d')));
            }

            if ($dividend->paymentDate !== null) {
                $output->writeln(sprintf('  Payment date: %s', $dividend->paymentDate->format('Y-m-d')));
            }

            $output->writeln(sprintf('  Type: %s', $dividend->dividendType));
            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
