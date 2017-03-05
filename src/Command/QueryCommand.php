<?php

namespace Cffie\Command;

use Cffie\Cff\CffClient;
use Joli\JoliNotif\Notification;
use Joli\JoliNotif\NotifierFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class QueryCommand extends Command
{
    /** @var CffClient */
    private $cff;

    /** @var SymfonyStyle */
    private $io;

    protected function configure()
    {
        $this
            ->setName('query')
            ->setDescription('Query SBB/CFF/FFS connections')
            ->addArgument('departure', InputArgument::REQUIRED, 'Departure stop')
            ->addArgument('arrival', InputArgument::REQUIRED, 'Arrival stop')
            ->addArgument('datetime', InputArgument::OPTIONAL, 'Datetime of travel', 'now')
            ->addOption('notify', null, InputOption::VALUE_NONE, 'Show desktop notification')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->io = new SymfonyStyle($input, $output);
        $debug = $output->getVerbosity() === OutputInterface::VERBOSITY_DEBUG;
        $this->cff = new CffClient($debug);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $date = new \DateTime($input->getArgument('datetime'));

        // Get departure/arrival stops
        $departure = $this->getStop($input->getArgument('departure'), 'Which departure ?');
        $arrival = $this->getStop($input->getArgument('arrival'), 'Which arrival ?');
        $io->title(sprintf('%s -> %s (%s)', $departure['value'], $arrival['value'], $date->format('d.m.y H:i')));

        // Query
        $times = $this->cff->query($departure, $arrival, $date);

        // Compute delays
        $delays = array();
        $now = new \DateTime();
        foreach ($times as $time) {
            $diff = $now->diff(\DateTime::createFromFormat('d.m.y H:i', $time['date'].' '.$time['departure']));
            $delays[] = (int) ($diff->format('%r').($diff->h * 60 + $diff->i));
        }

        // Show output table
        $formattedTimes = array_map(function($v, $d) {
            unset($v['date']);
            $v = array('in' => $d.'´') + $v;
            $v['infos'] = '<error>'.$v['infos'].'</error>';
            return $v;
        }, $times, $delays);
        $io->table(array('In', 'Dep.', 'Arr.', 'Dur.', 'Chg.', 'With', 'Infos'), $formattedTimes);

        // Show notification
        if ($input->getOption('notify') && $notifier = NotifierFactory::create()) {
            $body = '';
            foreach ($times as $t => $time) {
                $body .= sprintf("%s - %s | %s | %s chg. | %s | in %s´ %s\r\n", $time['departure'], $time['arrival'], $time['duration'], $time['change'], $time['product'], $delays[$t], $time['infos']);
            }
            $notification = (new Notification())
                ->setTitle(sprintf("%s -> %s", $departure['value'], $arrival['value']))
                ->setBody($body)
            ;
            $notifier->send($notification);
        }
    }

    private function getStop($value, $question = 'Which station ?')
    {
        $stops = $this->cff->getStop($value, false);

        if ($stops[0]['value'] != $value) {
            $values = array_column($stops, 'value');
            $value = $this->io->choice($question, $values, $stops[0]['value']);
            $key = array_search($value, $values);

            return $stops[$key];
        }

        return $stops[0];
    }
}