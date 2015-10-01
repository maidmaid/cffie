<?php

namespace Cffie\Command;

use Cffie\Cff\CffClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class QueryCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('query')
            ->setDescription('Query CFF connections')
            ->addArgument('departure', InputArgument::REQUIRED, 'departure city')
            ->addArgument('arrival', InputArgument::REQUIRED, 'arrival city')
            ->addArgument('datetime', InputArgument::OPTIONAL, 'datetime of travel', 'now')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output = new SymfonyStyle($input, $output);
        $debug = $output->getVerbosity() === OutputInterface::VERBOSITY_DEBUG;
        $cff = new CffClient($debug);

        $date = new \DateTime($input->getArgument('datetime'));
        $departure = $cff->getStop($input->getArgument('departure'));
        $arrival = $cff->getStop($input->getArgument('arrival'));
        $output->title(sprintf('%s -> %s (%s)', $departure['value'], $arrival['value'], $date->format('d:m:Y H:i:s')));

        $times = $cff->query($departure, $arrival, $date);
        $output->table(array('Departure', 'Arrival', 'Duration', 'Chg.', 'Travel with'), $times);
    }
}