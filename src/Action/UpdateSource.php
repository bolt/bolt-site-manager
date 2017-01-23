<?php

namespace Bolt\Deploy\Action;

use Bolt\Deploy\Util\Git;
use Composer\Console\Application as ComposerApplication;
use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * Update source repository action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class UpdateSource extends AbstractAction
{
    /** @var Git */
    protected $git;

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->git = new Git($this->config, $this->siteConfig);
        $composerRoot = $this->siteConfig->getPath('source');

        $this->getRemoteUpdate();
        $this->getCheckoutBranch();
        $this->gitPull();
        $this->composerInstall($composerRoot);
        $this->composerInstall($composerRoot . '/extensions');
    }

    /**
     * Update the local git database with all it's remotes data.
     */
    protected function getRemoteUpdate()
    {
        $this->assertGitRepo();
        $this->git->remote('update');
    }

    /**
     * Checkout the requested branch, or default to master.
     */
    protected function getCheckoutBranch()
    {
        $branch = 'master';
        if ($this->input->hasOption('branch')) {
            $branch = $this->input->getOption('branch');
        }

        $this->assertGitRepo();
        $this->git->checkout($branch);
    }

    /**
     * Update git repository.
     *
     * @throws \RuntimeException
     */
    protected function gitPull()
    {
        $this->assertGitRepo();
        $this->assertGitRepoClean();
        $this->git->pull();
    }

    /**
     * Execute a `composer install` in the repository.
     *
     * @param string $composerRoot
     *
     * @throws \Exception
     */
    protected function composerInstall($composerRoot)
    {
        $cwd = getcwd();
        chdir($composerRoot);

        putenv('COMPOSER_ALLOW_SUPERUSER=1');
        putenv('COMPOSER_DISABLE_XDEBUG_WARN=1');

        $argv = new ArgvInput(['', 'install', '--classmap-authoritative', '--prefer-dist', '--no-dev']);

        $composer = new ComposerApplication();
        $return = $composer->doRun($argv, $this->output);

        chdir($cwd);

        if ($return !== 0) {
            throw new \RuntimeException('Composer install did not complete.');
        }
    }

    /**
     * @throws RuntimeException
     */
    protected function assertGitRepo()
    {
        if (!file_exists($this->siteConfig->getPath('source') . '.git')) {
            throw new RuntimeException(sprintf('No git repository found at %s', $this->siteConfig->getPath('source')));
        }
    }

    /**
     * @throws RuntimeException
     */
    protected function assertGitRepoClean()
    {
        if (!$this->git->isWorkingCopyClean()) {
            throw new RuntimeException(sprintf('The git repository has uncommitted changes!', $this->siteConfig->getPath('source')));
        }
    }
}
