<?php

namespace Cffie\Console;

use Cffie\Command\CffCommand;
use Cffie\Command\QueryCommand;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new QueryCommand();

        return $commands;
    }
}