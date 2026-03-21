<?php

declare(strict_types=1);

namespace TInvest\Core\Command\Market;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Core\Helper\OutputFormatTrait;
use TInvest\Core\Service\TickerResolver\TickerResolverInterface;
use TInvest\Core\Service\MarketData\MarketDataServiceInterface;

#[AsCommand(
    name: 'market:orderbook',
    description: 'Get order book (glass) for an instrument',
)]
final class OrderbookCommand extends Command
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
            ->addOption('ticker', 't', InputOption::VALUE_REQUIRED, 'Ticker (e.g., SBER, GAZP)')
            ->addOption('figi', null, InputOption::VALUE_REQUIRED, 'Instrument FIGI')
            ->addOption('depth', 'd', InputOption::VALUE_OPTIONAL, 'Order book depth (1-50)', '20')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format: table, json, csv, md', 'table');
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

        $depth = (int)$input->getOption('depth');
        $depth = max(1, min(50, $depth));
        $format = $this->getFormat($input);

        $instrumentId = $this->tickerResolver->resolveTickerToUid($ticker);
        if ($instrumentId === null) {
            $output->writeln(sprintf('<error>Cannot resolve ticker: %s</error>', $ticker));
            return Command::FAILURE;
        }

        $orderBook = $this->marketDataService->getOrderBook($instrumentId, $depth);

        if ($format !== 'table') {
            $rows = [];
            foreach (array_reverse($orderBook->asks) as $ask) {
                $rows[] = ['ASK', number_format($ask->price, 4), (string)$ask->quantity];
            }
            foreach ($orderBook->bids as $bid) {
                $rows[] = ['BID', number_format($bid->price, 4), (string)$bid->quantity];
            }

            return $this->outputFormat(
                $output,
                $format,
                ['Side', 'Price', 'Quantity'],
                $rows,
                sprintf('Order Book for %s', $ticker)
            );
        }

        $output->writeln(sprintf(
            '<info>Order Book for %s (depth: %d)</info>',
            $ticker,
            $orderBook->depth
        ));
        $output->writeln(sprintf('<info>Time: %s</info>', $orderBook->time->format('Y-m-d H:i:s')));
        $output->writeln('');

        $output->writeln(sprintf('<comment>%-15s %12s</comment>', 'ASKS', ''));
        $output->writeln(sprintf('<info>%-15s %12s</info>', 'Price', 'Quantity'));
        foreach (array_reverse($orderBook->asks) as $ask) {
            $output->writeln(sprintf(
                '<fg=red>%-15.4f %12d</>',
                $ask->price,
                $ask->quantity
            ));
        }

        $output->writeln('<comment>─────────────────────────────</comment>');

        foreach ($orderBook->bids as $bid) {
            $output->writeln(sprintf(
                '<fg=green>%-15.4f %12d</>',
                $bid->price,
                $bid->quantity
            ));
        }
        $output->writeln(sprintf('<comment>%-15s %12s</comment>', 'BIDS', ''));

        $output->writeln('');

        $spread = 0.0;
        if (count($orderBook->asks) > 0 && count($orderBook->bids) > 0) {
            $bestAsk = $orderBook->asks[0]->price;
            $bestBid = $orderBook->bids[0]->price;
            $spread = $bestAsk - $bestBid;
            $spreadPercent = $bestBid > 0 ? ($spread / $bestBid) * 100 : 0;

            $output->writeln(sprintf('<info>Spread: %.4f (%.2f%%)</info>', $spread, $spreadPercent));
        }

        $output->writeln(sprintf(
            '<info>Bids: %d | Asks: %d</info>',
            count($orderBook->bids),
            count($orderBook->asks)
        ));

        return Command::SUCCESS;
    }
}
