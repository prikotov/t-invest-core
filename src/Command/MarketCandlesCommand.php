<?php

declare(strict_types=1);

namespace TInvest\Skill\Command;

use DateTimeImmutable;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Skill\Component\TInvest\MarketDataService\Dto\GetCandlesRequestDto;
use TInvest\Skill\Component\TInvest\MarketDataService\Enum\CandleIntervalEnum;
use TInvest\Skill\Component\TInvest\MarketDataService\MarketDataServiceComponentInterface;

#[AsCommand(
    name: 'market:candles',
    description: 'Get historical candles for an instrument',
)]
final class MarketCandlesCommand extends Command
{
    public function __construct(
        private readonly MarketDataServiceComponentInterface $marketDataService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument('instrument', InputArgument::REQUIRED, 'Instrument ID (figi, instrumentUid or ticker)')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Start date (Y-m-d)', '-7 days')
            ->addOption('to', 't', InputOption::VALUE_OPTIONAL, 'End date (Y-m-d)', 'now')
            ->addOption('interval', 'i', InputOption::VALUE_OPTIONAL, 'Candle interval', '1h')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Max candles', '100');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $instrumentId = $input->getArgument('instrument');
        $fromStr = $input->getOption('from');
        $toStr = $input->getOption('to');
        $intervalStr = $input->getOption('interval');
        $limit = (int)$input->getOption('limit');

        $from = new DateTimeImmutable($fromStr);
        $to = new DateTimeImmutable($toStr);
        $interval = $this->parseInterval($intervalStr);

        $request = new GetCandlesRequestDto($instrumentId, $from, $to, $interval, $limit);
        $response = $this->marketDataService->getCandles($request);

        if (empty($response->candles)) {
            $output->writeln('<comment>No candles found</comment>');
            return Command::SUCCESS;
        }

        $output->writeln(sprintf(
            '<info>Candles for %s (%s to %s):</info>',
            $instrumentId,
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

        foreach ($response->candles as $candle) {
            $output->writeln(sprintf(
                '%-20s %10.2f %10.2f %10.2f %10.2f %12d',
                $candle->time->format('Y-m-d H:i:s'),
                $candle->open->value,
                $candle->high->value,
                $candle->low->value,
                $candle->close->value,
                $candle->volume
            ));
        }

        $output->writeln('');
        $output->writeln(sprintf('<info>Total candles: %d</info>', count($response->candles)));

        return Command::SUCCESS;
    }

    private function parseInterval(string $interval): CandleIntervalEnum
    {
        return match ($interval) {
            '5s' => CandleIntervalEnum::FIVE_SEC,
            '10s' => CandleIntervalEnum::TEN_SEC,
            '30s' => CandleIntervalEnum::THIRTY_SEC,
            '1m', '1min' => CandleIntervalEnum::ONE_MIN,
            '2m', '2min' => CandleIntervalEnum::TWO_MIN,
            '3m', '3min' => CandleIntervalEnum::THREE_MIN,
            '5m', '5min' => CandleIntervalEnum::FIVE_MIN,
            '10m', '10min' => CandleIntervalEnum::TEN_MIN,
            '15m', '15min' => CandleIntervalEnum::FIFTEEN_MIN,
            '30m', '30min' => CandleIntervalEnum::THIRTY_MIN,
            '1h', '1hour' => CandleIntervalEnum::HOUR,
            '2h', '2hour' => CandleIntervalEnum::TWO_HOUR,
            '4h', '4hour' => CandleIntervalEnum::FOUR_HOUR,
            '1d', 'day' => CandleIntervalEnum::DAY,
            '1w', 'week' => CandleIntervalEnum::WEEK,
            '1M', 'month' => CandleIntervalEnum::MONTH,
            default => CandleIntervalEnum::HOUR,
        };
    }
}
