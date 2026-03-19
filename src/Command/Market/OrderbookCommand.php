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
use TInvest\Core\Service\MarketData\MarketDataServiceInterface;

#[AsCommand(
    name: 'market:orderbook',
    description: 'Get order book (glass) for an instrument',
)]
final class OrderbookCommand extends Command
{
    public function __construct(
        private readonly MarketDataServiceInterface $marketDataService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument('instrument', InputArgument::REQUIRED, 'Instrument ID (figi, instrumentUid or ticker)')
            ->addOption('depth', 'd', InputOption::VALUE_OPTIONAL, 'Order book depth (1-50)', '20');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $instrumentId = $input->getArgument('instrument');
        $depth = (int)$input->getOption('depth');
        $depth = max(1, min(50, $depth));

        $orderBook = $this->marketDataService->getOrderBook($instrumentId, $depth);

        $output->writeln(sprintf(
            '<info>Order Book for %s (depth: %d)</info>',
            $instrumentId,
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
