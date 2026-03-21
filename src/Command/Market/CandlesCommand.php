<?php

declare(strict_types=1);

namespace TInvest\Core\Command\Market;

use DateTimeImmutable;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Core\Service\TickerResolver\TickerResolverInterface;
use TInvest\Core\Service\MarketData\MarketDataServiceInterface;

#[AsCommand(
    name: 'market:candles',
    description: 'Get historical candles for an instrument',
)]
final class CandlesCommand extends Command
{
    public function __construct(
        private readonly MarketDataServiceInterface $marketDataService,
        private readonly TickerResolverInterface $tickerResolver,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addOption('ticker', 't', InputOption::VALUE_REQUIRED, 'Ticker (e.g., SBER, GAZP)')
            ->addOption('figi', null, InputOption::VALUE_REQUIRED, 'Instrument FIGI')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Start date (Y-m-d)', '-7 days')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'End date (Y-m-d)', 'now')
            ->addOption('interval', 'i', InputOption::VALUE_OPTIONAL, 'Candle interval', '1h')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Max candles', '100');
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

        $fromStr = $input->getOption('from');
        $toStr = $input->getOption('to');
        $intervalStr = $input->getOption('interval');
        $limit = (int)$input->getOption('limit');

        $instrumentId = $this->tickerResolver->resolveTickerToUid($ticker);
        if ($instrumentId === null) {
            $output->writeln(sprintf('<error>Cannot resolve ticker: %s</error>', $ticker));
            return Command::FAILURE;
        }

        $from = new DateTimeImmutable($fromStr);
        $to = new DateTimeImmutable($toStr);

        $candles = [];
        foreach ($this->marketDataService->getCandles($instrumentId, $from, $to, $intervalStr, $limit) as $candle) {
            $candles[] = $candle;
        }

        if ($candles === []) {
            $output->writeln('<comment>No candles found</comment>');
            return Command::SUCCESS;
        }

        $output->writeln(sprintf(
            '<info>Candles for %s (%s to %s):</info>',
            $ticker,
            $from->format('Y-m-d H:i'),
            $to->format('Y-m-d H:i')
        ));
        $output->writeln('');

        $output->writeln(sprintf(
            '<info>%-20s %10s %10s %10s %10s %12s</info>',
            'Time',
            'Open',
            'High',
            'Low',
            'Close',
            'Volume'
        ));

        foreach ($candles as $candle) {
            $output->writeln(sprintf(
                '%-20s %10.2f %10.2f %10.2f %10.2f %12d',
                $candle->time->format('Y-m-d H:i:s'),
                $candle->open,
                $candle->high,
                $candle->low,
                $candle->close,
                $candle->volume
            ));
        }

        $output->writeln('');
        $output->writeln(sprintf('<info>Total candles: %d</info>', count($candles)));

        return Command::SUCCESS;
    }
}
