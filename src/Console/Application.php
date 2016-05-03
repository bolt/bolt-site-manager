<?php

namespace Bolt\Deploy\Console;

use Bolt\Deploy\Command\CreateConfigCommand;
use Bolt\Deploy\Command\DeployCommand;
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

    static $timestamp;

    /**
     * Constructor.
     */
    public function __construct($name = 'site-deploy')
    {
        parent::__construct($name, self::VERSION);
        self::$timestamp = Carbon::now()->format('Ymd-His');

        $this->addCommands([
            new CreateConfigCommand(),
            new DeployCommand(),
        ]);
    }
}
