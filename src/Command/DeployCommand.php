<?php

namespace Bolt\Deploy\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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

        echo "We have the technology\n";
    }
}
