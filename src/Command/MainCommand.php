<?php

declare(strict_types=1);

namespace TInvest\Skill\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'main',
    description: 'T-Invest Skill CLI',
)]
final class MainCommand extends Command
{
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>T-Invest Skill</info>');
        $output->writeln('<comment>Use --help to see available commands</comment>');

        return Command::SUCCESS;
    }
}
