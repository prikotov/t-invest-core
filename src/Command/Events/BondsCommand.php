<?php

declare(strict_types=1);

namespace TInvest\Core\Command\Events;

use DateTimeImmutable;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Core\Service\Instruments\InstrumentsServiceInterface;
use TInvest\Core\Service\TickerResolver\TickerResolverInterface;

#[AsCommand(
    name: 'events:bonds',
    description: 'Get bond events (coupons, maturity, calls)',
)]
final class BondsCommand extends Command
{
    public function __construct(
        private readonly InstrumentsServiceInterface $instrumentsService,
        private readonly TickerResolverInterface $tickerResolver,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addOption('ticker', 't', InputOption::VALUE_REQUIRED, 'Bond ticker')
            ->addOption('figi', null, InputOption::VALUE_REQUIRED, 'Bond FIGI')
            ->addOption('type', null, InputOption::VALUE_OPTIONAL, 'Event type (CPN, CALL, MTY, CONV)')
            ->addOption('from', null, InputOption::VALUE_OPTIONAL, 'From date (YYYY-MM-DD)')
            ->addOption('to', null, InputOption::VALUE_OPTIONAL, 'To date (YYYY-MM-DD)');
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

        $type = $input->getOption('type');
        $from = $input->getOption('from');
        $to = $input->getOption('to');

        $eventType = is_string($type) && $type !== '' ? 'EVENT_TYPE_' . $type : null;
        $fromDate = is_string($from) ? new DateTimeImmutable($from) : null;
        $toDate = is_string($to) ? new DateTimeImmutable($to) : null;

        $events = $this->instrumentsService->getBondEvents($ticker, $eventType, $fromDate, $toDate);

        if ($events === []) {
            $output->writeln(sprintf('<comment>No bond events found for %s</comment>', $ticker));
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<info>Bond events for %s</info>', $ticker));
        $output->writeln('');

        foreach ($events as $event) {
            $typeLabel = match (str_replace('EVENT_TYPE_', '', $event->eventType)) {
                'CPN' => 'Coupon',
                'CALL' => 'Call (Offer)',
                'MTY' => 'Maturity',
                'CONV' => 'Conversion',
                default => $event->eventType,
            };

            $output->writeln(sprintf(
                '<info>%s</info> | #%d | %s',
                $event->eventDate?->format('Y-m-d') ?? 'TBD',
                $event->eventNumber,
                $typeLabel,
            ));

            if ($event->payOneBond !== null) {
                $output->writeln(sprintf(
                    '  Payment: %s %s',
                    number_format($event->payOneBond, 2),
                    $event->currency ?? 'N/A',
                ));
            }

            if ($event->couponPeriod !== null) {
                $output->writeln(sprintf('  Period: %d days', $event->couponPeriod));
            }

            if ($event->couponInterestRate !== null) {
                $output->writeln(sprintf('  Rate: %s%%', number_format($event->couponInterestRate, 2)));
            }

            if ($event->note !== null) {
                $output->writeln(sprintf('  Note: %s', $event->note));
            }

            $output->writeln('');
        }

        return Command::SUCCESS;
    }
}
