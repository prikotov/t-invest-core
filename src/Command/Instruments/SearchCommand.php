<?php

declare(strict_types=1);

namespace TInvest\Core\Command\Instruments;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TInvest\Core\Helper\OutputFormatTrait;
use TInvest\Core\Service\Instruments\InstrumentsServiceInterface;

#[AsCommand(
    name: 'instruments:search',
    description: 'Search instruments by name or ticker',
)]
final class SearchCommand extends Command
{
    use OutputFormatTrait;

    public function __construct(
        private readonly InstrumentsServiceInterface $instrumentsService,
    ) {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument('query', InputArgument::REQUIRED, 'Search query (name or ticker)')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit results', 20)
            ->addOption('format', 'f', InputOption::VALUE_OPTIONAL, 'Output format: table, json, csv, md', 'table');
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $query */
        $query = $input->getArgument('query');
        /** @var int $limit */
        $limit = (int)$input->getOption('limit');
        $format = $this->getFormat($input);

        $instruments = $this->instrumentsService->search($query);

        if (empty($instruments)) {
            $output->writeln('<comment>No instruments found</comment>');
            return Command::SUCCESS;
        }

        $instruments = array_slice($instruments, 0, $limit);

        if ($format !== 'table') {
            $rows = array_map(fn($instrument) => [
                $instrument->ticker,
                $instrument->instrumentType,
                $instrument->classCode,
                $instrument->apiTradeAvailableFlag ? 'yes' : 'no',
            ], $instruments);

            return $this->outputFormat(
                $output,
                $format,
                ['Ticker', 'Type', 'Class', 'Tradeable'],
                $rows,
                sprintf('Search results for "%s"', $query)
            );
        }

        foreach ($instruments as $instrument) {
            $output->writeln(sprintf(
                '<info>%-10s</info> %-15s [%s] %s',
                $instrument->ticker,
                $instrument->instrumentType,
                $instrument->classCode,
                $instrument->apiTradeAvailableFlag ? 'tradeable' : ''
            ));
        }

        $output->writeln('');
        $output->writeln(sprintf('<comment>Total: %d (showing %d)</comment>', count($this->instrumentsService->search($query)), count($instruments)));

        return Command::SUCCESS;
    }
}
