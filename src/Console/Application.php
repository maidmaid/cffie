<?php

namespace Cffie\Console;

use Cffie\Command\QueryCommand;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    private $logo = '   ____ _____ _____ _
  / ___|  ___|  ___(_) ___
 | |   | |_  | |_  | |/ _ \
 | |___|  _| |  _| | |  __/
  \____|_|   |_|   |_|\___|

';

    public function __construct()
    {
        parent::__construct('CFFie', '0.1.1');
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new QueryCommand();

        return $commands;
    }

    public function getHelp()
    {
        return $this->logo.parent::getHelp();
    }
}
