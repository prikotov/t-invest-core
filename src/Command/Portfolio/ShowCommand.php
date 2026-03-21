<?php

declare(strict_types=1);

namespace TInvest\Core\Command\Portfolio;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Core\Helper\OutputFormatTrait;
use TInvest\Core\Service\Operations\OperationsServiceInterface;

#[AsCommand(
    name: 'portfolio:show',
    description: 'Show portfolio with optional ticker filter',
)]
final class ShowCommand extends Command
{
    use OutputFormatTrait;

    public function __construct(
        private readonly OperationsServiceInterface $operationsService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addOption('ticker', 't', InputOption::VALUE_OPTIONAL, 'Filter by ticker')
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format: table, json, csv, md', 'table');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ticker = $input->getOption('ticker');
        $format = $this->getFormat($input);
        $portfolio = $this->operationsService->getPortfolio($ticker);

        if ($portfolio->positions === []) {
            $message = $ticker !== null
                ? sprintf('<comment>No position found for ticker: %s</comment>', $ticker)
                : '<comment>No positions found</comment>';
            $output->writeln($message);
            return Command::SUCCESS;
        }

        if ($format !== 'table') {
            $rows = array_map(fn($position) => [
                $position->ticker,
                $position->instrumentType,
                number_format($position->quantity, 2),
                number_format($position->avgPrice, 2),
                number_format($position->expectedYield, 2),
                number_format($position->currentPrice, 2),
            ], $portfolio->positions);

            return $this->outputFormat(
                $output,
                $format,
                ['Ticker', 'Type', 'Quantity', 'AvgPrice', 'Yield%', 'CurrentPrice'],
                $rows,
                'Portfolio'
            );
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
