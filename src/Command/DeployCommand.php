<?php

namespace Bolt\Deploy\Command;

use Bolt\Deploy\Config\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * Deploy command class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DeployCommand extends AbstractCommand
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

        $output->writeln('<comment>Starting deployment process…</comment>');

        // Turn on sudo early
        $this->doActivateSudo();

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
}
