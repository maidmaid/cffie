<?php

namespace Cffie\Command;

use Cffie\Cff\CffClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AlertCommand extends Command
{
    /** @var CffClient */
    private $cff;

    /** @var SymfonyStyle */
    private $output;

    protected function configure()
    {
        $this
            ->setName('alert')
            ->setDescription('Alert by mail for travel infos')

            ->addArgument('departure', InputArgument::REQUIRED, 'departure stop')
            ->addArgument('arrival', InputArgument::REQUIRED, 'arrival stop')
            ->addArgument('time', InputArgument::REQUIRED, 'time of travel')

            ->addOption('host', null, InputOption::VALUE_REQUIRED, 'host of mail server', 'smtp.gmail.com')
            ->addOption('port', null, InputOption::VALUE_REQUIRED, 'port of mail server', 465)
            ->addOption('security', null, InputOption::VALUE_REQUIRED, 'security of mail server', 'ssl')
            ->addOption('username', null, InputOption::VALUE_REQUIRED, 'username of mail server')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'password of mail server')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'recipient of mail server')
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
        $date = new \DateTime($input->getArgument('time'));

        // Get departure/arrival stops
        $departure = $this->cff->getStop($input->getArgument('departure'));
        $arrival = $this->cff->getStop($input->getArgument('arrival'));
        $output->write(sprintf('%s -> %s at %s : ', $departure['value'], $arrival['value'], $date->format('H:s')));

        $times = $this->cff->query($departure, $arrival, $date);
        $times = array_filter($times, function($value) use ($date) {
            return (new \DateTime($value['departure'])) == $date;
        });

        if ($infos = current($times)['infos']) {
            $output->writeln(sprintf('<comment>%s</comment>', $infos));

            // Create the Transport
            $transport = \Swift_SmtpTransport::newInstance($input->getOption('host'), $input->getOption('port'), $input->getOption('security'))
                ->setUsername($input->getOption('username'))
                ->setPassword($input->getOption('password'))
            ;
            $mailer = \Swift_Mailer::newInstance($transport);

            // Create a message
            $message = \Swift_Message::newInstance($infos)
                ->setFrom(array($input->getOption('username') => 'CFFie'))
                ->setTo(array($input->getOption('to')))
                ->setBody(sprintf("%s -> %s at %s\nCFFie", $departure['value'], $arrival['value'], $date->format('H:m')))
            ;

            // Send the message
            $result = $mailer->send($message);
            if ($result) {
                $this->output->success($input->getOption('to').' alerted !');
            }
        } else {
            $output->writeln('<info>ok</info>');
        }
    }
}