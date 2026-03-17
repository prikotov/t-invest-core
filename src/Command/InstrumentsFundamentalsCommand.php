<?php

declare(strict_types=1);

namespace TInvest\Skill\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Skill\Service\Instruments\InstrumentsServiceInterface;

#[AsCommand(
    name: 'instruments:fundamentals',
    description: 'Get fundamental indicators for instruments by tickers',
)]
final class InstrumentsFundamentalsCommand extends Command
{
    public function __construct(
        private readonly InstrumentsServiceInterface $instrumentsService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this->addArgument('tickers', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'Tickers (space-separated)');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var list<string> $tickers */
        $tickers = $input->getArgument('tickers');

        $fundamentals = $this->instrumentsService->getFundamentalsByTickers($tickers);

        if (empty($fundamentals)) {
            $output->writeln('<comment>No fundamentals found for given tickers</comment>');
            return Command::SUCCESS;
        }

        $output->writeln('<info>Fundamentals:</info>');
        $output->writeln('');

        foreach ($fundamentals as $fundamental) {
            $output->writeln(sprintf('<info>=== %s ===</info>', $fundamental->ticker));
            $output->writeln('');

            $this->printValue($output, 'Currency', $fundamental->currency);
            $this->printValue($output, 'Market Cap', $fundamental->marketCapitalization, true);
            $this->printValue($output, 'P/E', $fundamental->peRatioTtm);
            $this->printValue($output, 'P/B', $fundamental->priceToBookTtm);
            $this->printValue($output, 'P/S', $fundamental->priceToSalesTtm);
            $this->printValue($output, 'ROE', $fundamental->roe, false, '%');
            $this->printValue($output, 'ROA', $fundamental->roa, false, '%');
            $this->printValue($output, 'Div Yield', $fundamental->dividendYieldDailyTtm, false, '%');
            $this->printValue($output, 'EPS', $fundamental->epsTtm, true);
            $this->printValue($output, 'Revenue', $fundamental->revenueTtm, true);
            $this->printValue($output, 'Net Income', $fundamental->netIncomeTtm, true);
            $this->printValue($output, 'EBITDA', $fundamental->ebitdaTtm, true);
            $this->printValue($output, 'Free Cash Flow', $fundamental->freeCashFlowTtm, true);
            $this->printValue($output, 'Beta', $fundamental->beta);
            $this->printValue($output, '52W High', $fundamental->highPriceLast52Weeks, true);
            $this->printValue($output, '52W Low', $fundamental->lowPriceLast52Weeks, true);

            $output->writeln('');
        }

        return Command::SUCCESS;
    }

    private function printValue(OutputInterface $output, string $label, mixed $value, bool $formatMoney = false, string $suffix = ''): void
    {
        if ($value === null) {
            return;
        }

        if ($formatMoney && is_float($value)) {
            $formatted = $this->formatMoney($value);
            $output->writeln(sprintf('  %-15s: %s%s', $label, $formatted, $suffix));
        } else {
            $output->writeln(sprintf('  %-15s: %s%s', $label, (string)$value, $suffix));
        }
    }

    private function formatMoney(float $value): string
    {
        if ($value >= 1_000_000_000_000) {
            return number_format($value / (float)1_000_000_000_000, 2) . 'T';
        }
        if ($value >= 1_000_000_000) {
            return number_format($value / (float)1_000_000_000, 2) . 'B';
        }
        if ($value >= 1_000_000) {
            return number_format($value / (float)1_000_000, 2) . 'M';
        }
        return number_format($value, 2);
    }
}
