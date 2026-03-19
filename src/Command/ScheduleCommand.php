<?php

declare(strict_types=1);

namespace TInvest\Skill\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Skill\Component\TInvest\InstrumentsService\Dto\TradingScheduleRequestDto;
use TInvest\Skill\Component\TInvest\InstrumentsService\InstrumentsServiceComponentInterface;

#[AsCommand(
    name: 'schedule',
    description: 'Get trading schedule from T-Invest API',
)]
final class ScheduleCommand extends Command
{
    public function __construct(
        private readonly InstrumentsServiceComponentInterface $instrumentsService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addOption('exchange', 'e', InputOption::VALUE_OPTIONAL, 'Exchange code', 'MOEX')
            ->addOption('date', 'd', InputOption::VALUE_OPTIONAL, 'Date (YYYY-MM-DD)', date('Y-m-d'))
            ->addOption('days', null, InputOption::VALUE_OPTIONAL, 'Number of days', '7');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $exchange = $input->getOption('exchange');
        $from = $input->getOption('date');
        $days = (int)$input->getOption('days');

        $to = date('Y-m-d', strtotime($from . ' +' . $days . ' days'));

        $request = new TradingScheduleRequestDto(
            exchange: $exchange,
            from: $from,
            to: $to,
        );

        $schedule = $this->instrumentsService->getTradingSchedule($request);

        $output->writeln(sprintf('<info>Trading Schedule: %s</info>', $schedule->exchange));
        $output->writeln('');

        foreach ($schedule->days as $day) {
            $dateStr = $day->date->format('Y-m-d (D)');

            if (!$day->isTradingDay) {
                $output->writeln(sprintf(
                    '<comment>%s: NON-TRADING DAY</comment>%s',
                    $dateStr,
                    $day->holidayName ? ' - ' . $day->holidayName : ''
                ));
                continue;
            }

            $output->writeln(sprintf('<info>%s: Trading Day</info>', $dateStr));

            if ($day->morningSessionStart && $day->morningSessionEnd) {
                $output->writeln(sprintf(
                    '  Morning auction: %s - %s',
                    $day->morningSessionStart->format('H:i'),
                    $day->morningSessionEnd->format('H:i')
                ));
            }

            if ($day->startTime && $day->endTime) {
                $output->writeln(sprintf(
                    '  Main session: %s - %s',
                    $day->startTime->format('H:i'),
                    $day->endTime->format('H:i')
                ));
            }

            if ($day->clearingStart && $day->clearingEnd) {
                $output->writeln(sprintf(
                    '  Clearing: %s - %s',
                    $day->clearingStart->format('H:i'),
                    $day->clearingEnd->format('H:i')
                ));
            }

            if ($day->eveningSessionStart && $day->eveningSessionEnd) {
                $output->writeln(sprintf(
                    '  Evening session: %s - %s',
                    $day->eveningSessionStart->format('H:i'),
                    $day->eveningSessionEnd->format('H:i')
                ));
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
