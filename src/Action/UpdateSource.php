<?php

namespace Bolt\Deploy\Action;

use Bolt\Deploy\Config\Site;
use Bolt\Deploy\Util\Git;
use Composer\Console\Application as ComposerApplication;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Update source repository action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class UpdateSource implements ActionInterface
{
    /** @var string */
    protected $sourcePath;

    /**
     * Constructor.
     *
     * @param Site $siteConfig
     */
    public function __construct(Site $siteConfig)
    {
        $this->sourcePath = $siteConfig->getPath('source');
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->gitPull();
        $this->composerInstall();
    }

    /**
     * Update git repository.
     *
     * @throws \RuntimeException
     */
    protected function gitPull()
    {
        if (!file_exists($this->sourcePath . '.git')) {
            throw new \RuntimeException(sprintf('No git repository found at %s', $this->sourcePath));
        }

        $git = new Git($this->sourcePath);
        $git->pull();
    }

    /**
     * Execute a `composer install` in the repository.
     *
     * @throws \RuntimeException
     */
    protected function composerInstall()
    {
        $cwd = getcwd();
        chdir($this->sourcePath);

        putenv('COMPOSER_ALLOW_SUPERUSER=1');
        putenv('COMPOSER_DISABLE_XDEBUG_WARN=1');

        $composer = new ComposerApplication();
        $return = $composer->doRun(new ArgvInput(['', 'install']), new ConsoleOutput());

        chdir($cwd);

        if ($return !== 0) {
            throw new \RuntimeException('Composer install did not complete.');
        }
    }
}
