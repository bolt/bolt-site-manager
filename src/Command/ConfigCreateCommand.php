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
class ConfigCreateCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('config:create')
            ->setDescription('Configuration file creation')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('file-name', null, InputOption::VALUE_REQUIRED, 'File name of new configuration file'),
                ])
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('file-name')) {
            $fileName = $input->getOption('file-name');
        }
    }
}
