<?php

declare(strict_types=1);

namespace TInvest\Core\Command\Accounts;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Core\Service\Accounts\AccountsServiceInterface;

#[AsCommand(
    name: "accounts:list",
    description: 'Get list of user accounts',
)]
final class ListCommand extends Command
{
    public function __construct(
        private readonly AccountsServiceInterface $accountsService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $accounts = $this->accountsService->getAccounts();

        $rows = [];
        foreach ($accounts as $account) {
            $rows[] = [
                $account->id,
                $account->name,
                $account->type,
                $account->status,
                $account->accessLevel,
            ];
        }

        if (empty($rows)) {
            $output->writeln('<comment>No accounts found</comment>');
            return Command::SUCCESS;
        }

        $output->writeln('<info>Accounts:</info>');
        $output->writeln('');

        $output->writeln(sprintf(
            '<info>%-20s %-30s %-15s %-15s %-15s</info>',
            'ID',
            'Name',
            'Type',
            'Status',
            'Access Level'
        ));

        foreach ($rows as $row) {
            $output->writeln(sprintf(
                '%-20s %-30s %-15s %-15s %-15s',
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
