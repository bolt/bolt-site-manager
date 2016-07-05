<?php

namespace Bolt\Deploy\Action;

use Bolt\Deploy\Config\Config;
use Bolt\Deploy\Config\Site;
use Bolt\Deploy\Util\Git;
use Composer\Console\Application as ComposerApplication;
use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Create Composer project action class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class CreateProject extends AbstractAction
{
    /** @var string */
    protected $siteName;
    /** @var string */
    protected $siteDir;

    /**
     * @param string $siteName
     *
     * @return CreateProject
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;

        return $this;
    }

    /**
     * @param string $siteDir
     *
     * @return CreateProject
     */
    public function setSiteDir($siteDir)
    {
        $this->siteDir = $siteDir;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->composerCreateProject();
        $this->updateGitIgnore();
        $this->gitSetup();
        $this->configSetup();
    }

    /**
     * Execute a `composer install` in the repository.
     *
     * @throws \Exception
     */
    protected function composerCreateProject()
    {
        $fs = new Filesystem();
        if (!$fs->exists(dirname($this->siteDir))) {
            $fs->mkdir(dirname($this->siteDir));
        }

        $cwd = getcwd();
        chdir(dirname($this->siteDir));

        putenv('COMPOSER_ALLOW_SUPERUSER=1');
        putenv('COMPOSER_DISABLE_XDEBUG_WARN=1');

        $argv = new ArgvInput([
            '',
            'create-project',
            'bolt/composer-install:^3.0',
            $this->siteDir,
            '--no-interaction',
            '--prefer-dist'
        ]);

        $composer = new ComposerApplication();
        $return = $composer->doRun($argv, $this->output);

        chdir($cwd);

        if ($return !== 0) {
            throw new \RuntimeException('Composer create-project did not complete.');
        }
    }

    /**
     * Create/update the git ignore file.
     */
    protected function updateGitIgnore()
    {
        $gitIgnore = [
            '# Bolt files & directories',
            'app/cache',
            'app/config/config_local.yml',
            'app/config/*.yml.dist',
            'app/database',
            'public/bolt-public/view',
            'public/files',
            'public/thumbs',
            'vendor/',
            '.htaccess',
            'bolt-site-manager.yml',
            '.bolt-site-manager.yml',
            '# Misc developer tools',
            'bower_components',
            'node_modules',
            '.idea',
            '.project',
            '.sass-cache',
            'composer.phar',
            '# Operating system specific',
            '.*.swp',
            '._*',
            '.DS_Store',
        ];
        $fs = new Filesystem();
        $gitIgnoreFile = $this->siteDir. '/.gitignore';
        $fs->dumpFile($gitIgnoreFile, implode("\n", $gitIgnore));
    }

    /**
     * Set up the git repository, add required files and commit.
     */
    protected function gitSetup()
    {
        $fs = new Filesystem();
        if ($fs->exists($this->siteDir . '/.git')) {
            throw new RuntimeException(sprintf('No git repository found at %s', $this->siteDir));
        }

        $siteConfig = $this->config->getSite('local');
        $paths = $siteConfig->getPaths();
        $paths['source'] = $this->siteDir;
        $siteConfig->setPaths($paths);
        $git = new Git($this->config, $siteConfig);
        $git->init();
        $git->add('.');
        $git->commit('.', 'Intial Bolt project installation');
    }

    /**
     * Dump a usable YAML file default in the project.
     */
    protected function configSetup()
    {
        //$defaults = $this->config->getDefaultConfig();
        $defaults['sites'] = $this->config->getDefaultSiteConfig(basename($this->siteDir));
        $yamlFileName = sprintf('%s/.bolt-site-manager.yml', $this->siteDir);
        file_put_contents($yamlFileName, Yaml::dump($defaults, 6, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));
    }
}
