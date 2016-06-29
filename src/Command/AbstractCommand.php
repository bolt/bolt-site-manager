<?php

namespace Bolt\Deploy\Command;

use Bolt\Deploy\Action;
use Bolt\Deploy\Config\Config;
use RuntimeException;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Base command class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractCommand extends Command
{
    /** @var string */
    protected $configFile;
    
    /**
     * Load configuration files.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return Config
     */
    protected function loadConfiguration(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasOption('config') && $input->getOption('config') !== null) {
            $this->configFile = $input->getOption('config');
        } elseif (file_exists(getcwd() . '/.site-deploy.yml')) {
            $this->configFile = getcwd() . '/.site-deploy.yml';
        } elseif (file_exists('/etc/site-deploy.yml')) {
            $this->configFile = '/etc/site-deploy.yml';
        } else {
            $this->configFile = getenv('HOME') . '/.site-deploy.yml';
        }

        $fs = new Filesystem();
        if (!$fs->exists($this->configFile)) {
            $output->writeln('<error>Unable to find valid configuration file.</error>');
            die();
        }

        try {
            return new Config($this->configFile);
        } catch (FileLoaderLoadException $e) {
            $output->writeln(sprintf('<error>%s</error>', stripslashes($e->getMessage())));
            die();
        } catch (InvalidConfigurationException $e) {
            $output->writeln(sprintf('<error>%s</error>', stripslashes($e->getMessage())));
            die();
        }
    }

    protected function doActivateSudo()
    {
        $process = new Process('sudo id > /dev/null');
        $process->run();
        if (!$process->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }
    }

    /**
     * @param string          $siteName
     * @param Config          $config
     * @param OutputInterface $output
     */
    protected function doUpdateSource($siteName, Config $config, OutputInterface $output)
    {
        $updateSource = new Action\UpdateSource($siteName, $config, $output);
        try {
            $updateSource->execute();
            $output->writeln(sprintf('<info>Successfully updated git repository.</info>', $siteName));
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to update source repository!</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }
    }

    /**
     * @param string          $siteName
     * @param Config          $config
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function doBackupFiles($siteName, Config $config, InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('skip-backup-files') === true) {
            return;
        }

        $backup = new Action\BackupFiles($siteName, $config, $output);
        try {
            $backup->execute();
            $output->writeln(sprintf('<info>Successfully backed up %s to %s</info>', $siteName, $backup->getBackupPath()));
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to backup site!</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }
    }

    /**
     * @param string          $siteName
     * @param Config          $config
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function doBackupDatabase($siteName, Config $config, InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('skip-backup-database') === true) {
            return;
        }

        $backup = new Action\BackupDatabase($siteName, $config, $output);
        try {
            $backup->execute();
            $output->writeln(sprintf('<info>Successfully backed up %s database to %s</info>', $siteName, $backup->getBackupFileName()));
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to backup site!</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }
    }

    /**
     * @param string          $siteName
     * @param Config          $config
     * @param OutputInterface $output
     */
    protected function doUpdateTarget($siteName, Config $config, OutputInterface $output)
    {
        $updateTarget = new Action\UpdateTarget($siteName, $config, $output);
        try {
            $updateTarget->execute();
            $output->writeln(sprintf('<info>Successfully synchronised %s with deployment copy.</info>', $siteName));
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to update site!</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }
    }

    /**
     * @param string          $siteName
     * @param Config          $config
     * @param OutputInterface $output
     */
    protected function doSetPermissions($siteName, Config $config, OutputInterface $output)
    {
        $setPermissions = new Action\SetPermissions($siteName, $config, $output);
        try {
            $setPermissions->execute();
            $output->writeln('<info>Successfully updated permissions & access control lists.</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to update permissions & access control lists!</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }
    }
}
