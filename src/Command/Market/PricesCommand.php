<?php

declare(strict_types=1);

namespace TInvest\Core\Command\Market;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Core\Service\TickerResolver\TickerResolverInterface;
use TInvest\Core\Service\MarketData\MarketDataServiceInterface;

#[AsCommand(
    name: 'market:prices',
    description: 'Get last prices for instruments',
)]
final class PricesCommand extends Command
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
            ->addArgument(
                'tickers',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Tickers (e.g., SBER GAZP)'
            );
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string> $tickers */
        $tickers = $input->getArgument('tickers');

        $instrumentIds = $this->tickerResolver->resolveTickersToUids($tickers);

        if ($instrumentIds === []) {
            $output->writeln('<error>Cannot resolve any tickers</error>');
            return Command::FAILURE;
        }

        $prices = [];
        foreach ($this->marketDataService->getLastPrices($instrumentIds) as $price) {
            $prices[] = $price;
        }

        if ($prices === []) {
            $output->writeln('<comment>No prices found</comment>');
            return Command::SUCCESS;
        }

        $output->writeln('<info>Last prices:</info>');
        $output->writeln('');

        $output->writeln(sprintf(
            '<info>%-15s %-10s %15s %-20s</info>',
            'FIGI',
            'Ticker',
            'Price',
            'Time'
        ));

        foreach ($prices as $lastPrice) {
            $time = $lastPrice->time?->format('Y-m-d H:i:s') ?? 'N/A';
            $output->writeln(sprintf(
                '%-15s %-10s %15.2f %-20s',
                $lastPrice->figi,
                $lastPrice->ticker ?? 'N/A',
                $lastPrice->price,
                $time
            ));
        }

        return Command::SUCCESS;
    }
}
