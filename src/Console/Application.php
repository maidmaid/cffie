<?php

namespace Cffie\Console;

use Cffie\Command\QueryCommand;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    public function __construct()
    {
        parent::__construct('CFFie', '0.1');
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new QueryCommand();

        return $commands;
    }
}