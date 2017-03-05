<?php

namespace Cffie\Cff\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

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

    public function testA()
    {
        $this->assertTrue(true);
    }
}
