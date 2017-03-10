<?php

namespace Cffie\Command;

use Carbon\Carbon;
use Cffie\Cff\CffClient;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TrackCommand extends Command
{
    /** @var CffClient */
    private $cff;

    /** @var SymfonyStyle */
    private $io;

    protected function configure()
    {
        $this
            ->setName('track')
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

        $stops = [
            'St-Maurice' => [
                'in' => ['hour' => null, 'way' => null],
                'out' => ['hour' => '20:00', 'way' => 'V1'], // 0
            ],
            'Lausanne' => [
                'in' => ['hour' => '21:00', 'way' => 'V2'],  // 60
                'out' => ['hour' => '21:05', 'way' => 'V3'],
            ],
            'Zurich' => [
                'in' => ['hour' => '23:00', 'way' => 'V4'], // 180
                'out' => ['hour' => null, 'way' => null],
            ],
        ];

        $width = 100;
        $start = new Carbon(reset($stops)['out']['hour']);
        $end = new Carbon(end($stops)['in']['hour']);
        $minutes = $start->diffInMinutes($end);
        $minutesByStop = array_map(function ($stop) use ($start) {
            return $stop['in']['hour'] ? $start->diffInMinutes(new Carbon($stop['in']['hour'])) : 0;
        }, $stops);
        $duration = end($minutesByStop);
        $positions = array_map(function ($minutes) use ($width, $duration) {
            return (int) ($width / $duration * $minutes);
        }, $minutesByStop);

        ProgressBar::setFormatDefinition('cffie', "%stops%\n╞%bar%╡\n%hours%\n%ways%\n%percent%%");
        $b = $io->createProgressBar($minutes);
        $b->setFormat('cffie');
        $b->setEmptyBarCharacter('═');
        $b->setBarCharacter('<info>═</info>');
        $b->setProgressCharacter('<info>╪</info>');
        $b->setBarWidth($width - strlen('╞╡') + 1);

        $stopsStr = $hoursStr = $waysStr = str_repeat(' ', $width);
        foreach ($positions as $stop => $position) {
            $stopsStr = substr_replace($stopsStr, $str = "┍ $stop", $position, strlen($str));

            $in = $stops[$stop]['in']['hour'];
            $out = $stops[$stop]['out']['hour'];
            $str = sprintf('%s┼%s', $in ? $in.' ' : '', $out ? ' '.$out : '');
            $hoursStr = substr_replace($hoursStr, $str, $position - strpos($str, '┼'), strlen($str));

            $in = $stops[$stop]['in']['way'];
            $out = $stops[$stop]['out']['way'];
            $str = sprintf('%s┴%s', $in ? $in.' ' : '', $out ? ' '.$out : '');
            $waysStr = substr_replace($waysStr, $str, $position - strpos($str, '┴'), strlen($str));
        }

        $b->setMessage($stopsStr, 'stops');
        $b->setMessage($hoursStr, 'hours');
        $b->setMessage($waysStr, 'ways');

        $io->writeln('');
        for ($i = 0; $i < $minutes; $i++) {
            $b->advance();
            usleep(100000);
        }
    }
}
