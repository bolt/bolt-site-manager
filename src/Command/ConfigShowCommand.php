<?php

namespace Bolt\Deploy\Command;

use Bolt\Deploy\Action;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Configuration file show command class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ConfigShowCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('config:show')
            ->setDescription('Configuration file content display')
            ->setDefinition(
                new InputDefinition([
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
