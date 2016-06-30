<?php

namespace Bolt\Deploy\Console;

use Bolt\Deploy\Command;
use Carbon\Carbon;
use Symfony\Component\Console\Application as BaseApplication;

/**
 * Deploy command application.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Application extends BaseApplication
{
    const VERSION = '1.0.0';

    public static $timestamp;

    /**
     * Constructor.
     */
    public function __construct($name = 'site-deploy')
    {
        parent::__construct($name, self::VERSION);
        self::$timestamp = Carbon::now()->format('Ymd-His');

        $this->addCommands([
            new Command\ConfigCreateCommand(),
            new Command\ConfigShowCommand(),
            new Command\CreateCommand(),
            new Command\DeployCommand(),
        ]);
    }
}
