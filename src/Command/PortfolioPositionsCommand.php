<?php

declare(strict_types=1);

namespace TInvest\Skill\Command;

use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Skill\Service\Portfolio\PortfolioServiceInterface;

final class PortfolioPositionsCommand extends Command
{
    public function __construct(
        private readonly PortfolioServiceInterface $portfolioService,
    ) {
        parent::__construct('portfolio:positions');
    }

    #[Override]
    protected function configure(): void
    {
        $this->setDescription('Get portfolio positions');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $positions = $this->portfolioService->getPositions();

        $rows = [];
        foreach ($positions as $position) {
            $rows[] = [
                $position->figi,
                $position->instrumentType,
                $position->quantity,
                $position->price,
                $position->yield >= 0 ? '+' . (string)$position->yield : (string)$position->yield,
            ];
        }

        if (empty($rows)) {
            $output->writeln('<comment>No positions found</comment>');
            return Command::SUCCESS;
        }

        $output->writeln('<info>Portfolio Positions:</info>');
        $output->writeln('');

        $output->writeln(sprintf(
            '<info>%-12s %-15s %10s %15s %12s</info>',
            'FIGI',
            'Type',
            'Quantity',
            'Price',
            'Yield'
        ));

        foreach ($rows as $row) {
            $output->writeln(sprintf(
                '%-12s %-15s %10.2f %15s %12.2f',
                $row[0],
                $row[1],
                $row[2],
                $row[3],
                $row[4]
            ));
        }

        return Command::SUCCESS;
    }
}
