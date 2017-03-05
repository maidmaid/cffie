<?php

namespace Cffie\Cff\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class IntegrationTest extends TestCase
{
    /** @var string The root directory */
    private $rootDir;
    /** @var Filesystem The Filesystem component */
    private $fs;

    public function setUp()
    {
        $this->rootDir = realpath(__DIR__.'/..');
        $this->fs = new Filesystem();

        if (!$this->fs->exists($this->rootDir.'/cffie.phar')) {
            throw new \RuntimeException(sprintf("'cffie.phar' file in the '%s' directory does not exist.", $this->rootDir));
        }
    }

    /**
     * Runs the given string as a command and returns the resulting output.
     *
     * @param string $command The name of the command to execute
     *
     * @return string The output of the command
     *
     * @throws ProcessFailedException If the command execution is not successful
     */
    private function runCommand($command)
    {
        $process = new Process($command);
        $process->setWorkingDirectory($this->rootDir);
        $process->mustRun();

        return $process->getOutput();
    }

    public function testDefaultCommand()
    {
        $output = $this->runCommand(sprintf('php cffie.phar'));
        $this->assertRegExp('/CFFie \d\.\d\.\d by Dany Maillard/', $output);
    }

    public function testVersion()
    {
        $output = $this->runCommand(sprintf('php cffie.phar --version'));
        $this->assertRegExp('/CFFie \d\.\d\.\d by Dany Maillard/', $output);
    }

    /**
     * @dataProvider getQueryProvider
     */
    public function testQueryCommand($args)
    {
        $output = $this->runCommand(sprintf('php cffie.phar query '.$args));
        $this->assertRegExp('/.* -> .* (.*)/', $output, 'title output');
        $this->assertRegExp('/In.*Dep..*Arr..*Dur..*Chg..*With.*Infos/', $output, 'header output');
        $this->assertRegExp('/\d´.*\d:\d.*\d:\d.*\d:\d.*\d/', $output, 'body output');
    }

    public function getQueryProvider()
    {
        return [
            ['Lausanne Zurich'],
            ['Lausanne Zurich 23:59'],
            ['Lausanne Zurich --notify'],
            ['"Sion, gare" "Berne Airport"'],
            ['"Sion, gare" "Berne Airport" "tomorrow 12:00"'],
            ['Lausann Zuric'],
        ];
    }
}
