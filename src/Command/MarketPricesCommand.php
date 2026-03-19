<?php

declare(strict_types=1);

namespace TInvest\Skill\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Skill\Service\MarketData\MarketDataServiceInterface;

#[AsCommand(
    name: 'market:prices',
    description: 'Get last prices for instruments',
)]
final class MarketPricesCommand extends Command
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
            ->addArgument(
                'instruments',
                InputArgument::IS_ARRAY | InputArgument::REQUIRED,
                'Instrument IDs (figi, instrumentUid or ticker)'
            );
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var array<string> $instrumentIds */
        $instrumentIds = $input->getArgument('instruments');

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
