<?php

namespace Bolt\Deploy\Command;

use Bolt\Deploy\Action;
use Bolt\Deploy\Config\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Deploy command class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DeployCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('deploy')
            ->setDescription('Deploy a site')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('name', InputArgument::REQUIRED, 'Name of the site to deploy.'),
                    new InputOption('config', null, InputOption::VALUE_REQUIRED, 'Optional configuration file override.'),
                    new InputOption('skip-backup-files', null, InputOption::VALUE_NONE, 'Skip backup of site files.'),
                    new InputOption('skip-backup-database', null, InputOption::VALUE_NONE, 'Skip backup of database.'),
                ])
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->loadConfiguration($input, $output);
        $siteName = $input->getArgument('name');

        if ($config->getSite($siteName) === null) {
            $output->writeln(sprintf('<error>No configuration for site "%s" found!</error>', $siteName));
            $output->writeln('<error>Exiting.</error>');
            die();
        }

        // Update the source from its git repository
        $this->doUpdateSource($siteName, $config, $output);

        // If enabled do file backups
        $this->doBackupFiles($siteName, $config, $input, $output);

        // If enabled do database backups
        $this->doBackupDatabase($siteName, $config, $input, $output);

        // Update the site target from the source
        $this->doUpdateTarget($siteName, $config, $output);

        // Set/reset permissions
        $this->doSetPermissions($siteName, $config, $output);
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
