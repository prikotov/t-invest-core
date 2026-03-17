<?php

declare(strict_types=1);

namespace TInvest\Skill\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Skill\Component\TInvest\OperationsService\OperationsServiceComponentInterface;

#[AsCommand(
    name: 'portfolio:show',
    description: 'Show portfolio with optional ticker filter',
)]
final class PortfolioShowCommand extends Command
{
    public function __construct(
        private readonly OperationsServiceComponentInterface $operationsService,
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
        $portfolio = $this->operationsService->getPortfolio();

        $positions = $portfolio->positions;

        if ($ticker !== null) {
            $positions = array_filter($positions, fn($p) => $p->ticker === $ticker);
        }

        if (empty($positions)) {
            $message = $ticker !== null
                ? sprintf('<comment>No position found for ticker: %s</comment>', $ticker)
                : '<comment>No positions found</comment>';
            $output->writeln($message);
            return Command::SUCCESS;
        }

        $output->writeln('<info>Portfolio:</info>');
        $output->writeln('');

        if ($portfolio->totalAmountPortfolio !== null) {
            $output->writeln(sprintf(
                '<info>Total Portfolio Value: %.2f %s</info>',
                $portfolio->totalAmountPortfolio->value,
                $portfolio->totalAmountPortfolio->currency
            ));
            $output->writeln(sprintf(
                '<info>Expected Yield: %.2f%%</info>',
                $portfolio->expectedYield->value
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

        foreach ($positions as $position) {
            $yield = $position->expectedYield->value;
            $yieldStr = $yield >= 0 ? '+' . number_format($yield, 2) : number_format($yield, 2);
            $avgPrice = $position->averagePositionPrice?->value ?? 0.0;

            $output->writeln(sprintf(
                '%-10s %-15s %10.2f %15.2f %12s %15.2f',
                $position->ticker,
                $position->instrumentType,
                $position->quantity->value,
                $avgPrice,
                $yieldStr,
                $position->currentPrice->value
            ));
        }

        return Command::SUCCESS;
    }
}
