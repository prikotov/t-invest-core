<?php

declare(strict_types=1);

namespace TInvest\Core\Command\Market;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Core\Helper\OutputFormatTrait;
use TInvest\Core\Service\TickerResolver\TickerResolverInterface;
use TInvest\Core\Service\MarketData\MarketDataServiceInterface;

#[AsCommand(
    name: 'market:prices',
    description: 'Get last prices for instruments',
)]
final class PricesCommand extends Command
{
    use OutputFormatTrait;

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
            )
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format: table, json, csv, md', 'table');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string> $tickers */
        $tickers = $input->getArgument('tickers');
        $format = $this->getFormat($input);

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

        if ($format !== 'table') {
            $rows = array_map(fn($lastPrice) => [
                $lastPrice->figi,
                $lastPrice->ticker ?? 'N/A',
                number_format($lastPrice->price, 2),
                $lastPrice->time?->format('Y-m-d H:i:s') ?? 'N/A',
            ], $prices);

            return $this->outputFormat(
                $output,
                $format,
                ['FIGI', 'Ticker', 'Price', 'Time'],
                $rows,
                'Last Prices'
            );
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
