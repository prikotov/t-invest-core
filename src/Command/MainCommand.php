<?php

declare(strict_types=1);

namespace TInvest\Skill\Command;

use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MainCommand extends Command
{
    public function __construct()
    {
        parent::__construct('main');
    }

    #[Override]
    protected function configure(): void
    {
        $this->setDescription('T-Invest Skill CLI');
        $this->setHelp('T-Invest Skill CLI application');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('<info>T-Invest Skill</info>');
        $output->writeln('<comment>Use --help to see available commands</comment>');

        return Command::SUCCESS;
    }
}
