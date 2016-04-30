<?php

namespace Bolt\Deploy\Command;

use Bolt\Deploy\Action;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\IOException;

/**
 * Deploy command class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DeployCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('deploy')
            ->setDescription('Deploy a site')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('name', InputArgument::REQUIRED, 'Name of the site to deploy.'),
                ])
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->loadConfiguration($output);
        $siteName = $input->getArgument('name');

        if ($config->getSite($siteName) === null) {
            $output->writeln(sprintf('<error>No configuration for site "%s" found!.</error>', $siteName));
            $output->writeln('<error>Exiting.</error>');
            die();
        }

        $updateSource = new Action\UpdateSource($config->getSite($siteName));
        try {
            $updateSource->execute();
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to update source repository.</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }

        $backup = new Action\Backup($config->getSite($siteName));
        try {
            $backup->execute();
            $output->writeln(sprintf('<info>Successfully backed up %s to %s</info>', $siteName, $backup->getBackupPath()));
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to backup site.</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }

        $updateTarget = new Action\UpdateTarget($config->getSite($siteName));
        try {
            $updateTarget->execute();
            $output->writeln(sprintf('<info>Successfully updated %s.</info>', $siteName));
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to update site.</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }

        echo "We have the technology\n";
    }
}
