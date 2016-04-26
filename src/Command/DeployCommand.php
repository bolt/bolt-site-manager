<?php

namespace Bolt\Deploy\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Deploy command class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class DeployCommand extends Command
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
        echo "We have the technology\n";
    }
}
