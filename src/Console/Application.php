<?php

namespace Cffie\Console;

use Cffie\Command\AlertCommand;
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
        parent::__construct('CFFie', '0.3.0');
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        $commands[] = new QueryCommand();
        $commands[] = new AlertCommand();

        return $commands;
    }

    public function getHelp()
    {
        return $this->logo.parent::getHelp();
    }

    public function getLongVersion()
    {
        return parent::getLongVersion().' by <comment>Dany Maillard</comment>';
    }
}
