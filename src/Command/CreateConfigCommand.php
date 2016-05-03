<?php

namespace Bolt\Deploy\Command;

use Bolt\Deploy\Action;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Configuration file creation command class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class CreateConfigCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('config')
            ->setDescription('Configuration file management')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('create', null, InputOption::VALUE_REQUIRED, 'Create a new configuration file'),
                    new InputOption('show', null, InputOption::VALUE_REQUIRED, 'Show the in-use configuration file'),
                ])
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
