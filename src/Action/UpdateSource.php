<?php

namespace Bolt\Deploy\Action;

use Bolt\Deploy\Config\Config;
use Bolt\Deploy\Config\Site;
use Bolt\Deploy\Util\Git;
use Composer\Console\Application as ComposerApplication;
use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Update source repository action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class UpdateSource implements ActionInterface
{
    /** @var Config */
    private $config;
    /** @var Site */
    private $siteConfig;

    /**
     * Constructor.
     *
     * @param Config $config
     * @param Site   $siteConfig
     */
    public function __construct(Config $config, Site $siteConfig)
    {
        $this->config = $config;
        $this->siteConfig = $siteConfig;
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
        if (!file_exists($this->siteConfig->getPath('source') . '.git')) {
            throw new RuntimeException(sprintf('No git repository found at %s', $this->siteConfig->getPath('source')));
        }

        $git = new Git($this->config, $this->siteConfig);
        if (!$git->isWorkingCopyClean()) {
            throw new RuntimeException(sprintf('The git repository has uncommitted changes!', $this->siteConfig->getPath('source')));
        }
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
        chdir($this->siteConfig->getPath('source'));

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
