<?php

namespace Bolt\Deploy\Console;

use Bolt\Deploy\Command\DeployCommand;
use Carbon\Carbon;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Deploy command application.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Application extends BaseApplication
{
    const VERSION = '1.0.0';

    static $timestamp;

    /**
     * Constructor.
     */
    public function __construct($name = 'deploy')
    {
        parent::__construct($name, self::VERSION);
        self::$timestamp = Carbon::now()->format('Ymd-His');
    }

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
