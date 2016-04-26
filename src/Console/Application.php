<?php

namespace Bolt\Deploy\Console;

use Bolt\Deploy\Command\DeployCommand;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

/**
 * Deploy command application.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Application extends BaseApplication
{
    /**
     * {@inheritdoc}
     */
    protected function getCommandName(InputInterface $input)
    {
        return 'deploy';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        // Keep the core default commands to have the HelpCommand
        $defaultCommands = parent::getDefaultCommands();
        $defaultCommands[] = new DeployCommand();

        return $defaultCommands;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition()
    {
        $inputDefinition = parent::getDefinition();
        $inputDefinition->setArguments();

        return $inputDefinition;
    }
}