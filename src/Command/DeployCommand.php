<?php

namespace Bolt\Deploy\Command;

use Bolt\Deploy\Action;
use Bolt\Deploy\Config\Config;
use Bolt\Deploy\Config\Site;
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
                    new InputOption('config', null, InputOption::VALUE_REQUIRED, 'Optional configuration file override. Defaults to ~/.deploy.yml'),
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
        $siteConfig = $config->getSite($siteName);

        if ($siteConfig === null) {
            $output->writeln(sprintf('<error>No configuration for site "%s" found!</error>', $siteName));
            $output->writeln('<error>Exiting.</error>');
            die();
        }

        $this->doUpdateSource($config, $siteConfig, $output);
        $this->doBackup($siteConfig, $output);
        $this->doUpdateTarget($siteConfig, $output);
        $this->doSetPermissions($config, $siteConfig, $output);
    }

    /**
     * @param Config          $config
     * @param Site            $siteConfig
     * @param OutputInterface $output
     */
    protected function doUpdateSource(Config $config, Site $siteConfig, OutputInterface $output)
    {
        $updateSource = new Action\UpdateSource($config, $siteConfig);
        try {
            $updateSource->execute();
            $output->writeln(sprintf('<info>Successfully updated git repository.</info>', $siteConfig->getName()));
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to update source repository!</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }
    }

    /**
     * @param Site            $siteConfig
     * @param OutputInterface $output
     */
    protected function doBackup(Site $siteConfig, OutputInterface $output)
    {
        $backup = new Action\Backup($siteConfig);
        try {
            $backup->execute();
            $output->writeln(sprintf('<info>Successfully backed up %s to %s</info>', $siteConfig->getName(), $backup->getBackupPath()));
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to backup site!</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }
    }

    /**
     * @param Site            $siteConfig
     * @param OutputInterface $output
     */
    protected function doUpdateTarget(Site $siteConfig, OutputInterface $output)
    {
        $updateTarget = new Action\UpdateTarget($siteConfig);
        try {
            $updateTarget->execute();
            $output->writeln(sprintf('<info>Successfully synchronised %s with deployment copy.</info>', $siteConfig->getName()));
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to update site!</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }
    }

    /**
     * @param Config          $config
     * @param Site            $siteConfig
     * @param OutputInterface $output
     */
    protected function doSetPermissions(Config $config, Site $siteConfig, OutputInterface $output)
    {
        $setPermissions = new Action\SetPermissions($config, $siteConfig);
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
