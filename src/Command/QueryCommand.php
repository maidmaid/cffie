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
    /** @var CffClient */
    private $cff;

    /** @var SymfonyStyle */
    private $output;

    protected function configure()
    {
        $this
            ->setName('query')
            ->setDescription('Query CFF connections')
            ->addArgument('departure', InputArgument::REQUIRED, 'departure stop')
            ->addArgument('arrival', InputArgument::REQUIRED, 'arrival stop')
            ->addArgument('datetime', InputArgument::OPTIONAL, 'datetime of travel', 'now')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->output = new SymfonyStyle($input, $output);
        $debug = $output->getVerbosity() === OutputInterface::VERBOSITY_DEBUG;
        $this->cff = new CffClient($debug);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $date = new \DateTime($input->getArgument('datetime'));

        // Get departure/arrival stops
        $departure = $this->getStop($input->getArgument('departure'), 'Which departure ?');
        $arrival = $this->getStop($input->getArgument('arrival'), 'Which arrival ?');
        $this->output->title(sprintf('%s -> %s (%s)', $departure['value'], $arrival['value'], $date->format('d:m:Y H:i:s')));

        // Query
        $times = $this->cff->query($departure, $arrival, $date);
        $times = array_map(function($v) {
            $v['infos'] = '<error>'.$v['infos'].'</error>';
            return $v;
        }, $times);
        $this->output->table(array('Departure', 'Arrival', 'Duration', 'Chg.', 'Travel with', 'Infos'), $times);
    }

    private function getStop($value, $question = 'Which station ?')
    {
        $stops = $this->cff->getStop($value, false);

        if ($stops[0]['value'] != $value) {
            $values = array_column($stops, 'value');
            $value = $this->output->choice($question, $values, $stops[0]['value']);
            $key = array_search($value, $values);

            return $stops[$key];
        }

        return $stops[0];
    }
}