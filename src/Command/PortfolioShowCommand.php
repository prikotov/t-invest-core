<?php

declare(strict_types=1);

namespace TInvest\Skill\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Skill\Service\Operations\OperationsServiceInterface;

#[AsCommand(
    name: 'portfolio:show',
    description: 'Show portfolio with optional ticker filter',
)]
final class PortfolioShowCommand extends Command
{
    public function __construct(
        private readonly OperationsServiceInterface $operationsService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addOption('ticker', 't', InputOption::VALUE_OPTIONAL, 'Filter by ticker');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ticker = $input->getOption('ticker');
        $portfolio = $this->operationsService->getPortfolio($ticker);

        if ($portfolio->positions === []) {
            $message = $ticker !== null
                ? sprintf('<comment>No position found for ticker: %s</comment>', $ticker)
                : '<comment>No positions found</comment>';
            $output->writeln($message);
            return Command::SUCCESS;
        }

        $output->writeln('<info>Portfolio:</info>');
        $output->writeln('');

        if ($portfolio->totalAmount !== null) {
            $output->writeln(sprintf(
                '<info>Total Portfolio Value: %.2f %s</info>',
                $portfolio->totalAmount,
                $portfolio->currency
            ));
            $output->writeln(sprintf(
                '<info>Expected Yield: %.2f%%</info>',
                $portfolio->expectedYield
            ));
            $output->writeln('');
        }

        $output->writeln(sprintf(
            '<info>%-10s %-15s %10s %15s %12s %15s</info>',
            'Ticker',
            'Type',
            'Quantity',
            'Avg Price',
            'Yield',
            'Current Price'
        ));

        foreach ($portfolio->positions as $position) {
            $yield = $position->expectedYield;
            $yieldStr = $yield >= 0 ? '+' . number_format($yield, 2) : number_format($yield, 2);

            $output->writeln(sprintf(
                '%-10s %-15s %10.2f %15.2f %12s %15.2f',
                $position->ticker,
                $position->instrumentType,
                $position->quantity,
                $position->avgPrice,
                $yieldStr,
                $position->currentPrice
            ));
        }

        return Command::SUCCESS;
    }
}
