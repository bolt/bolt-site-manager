<?php

namespace Bolt\Deploy\Command;

use Bolt\Deploy\Action;
use Bolt\Deploy\Config\Config;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create deployable site command class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class CreateCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('create')
            ->setDescription('Create a new site install')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument('dir', InputArgument::REQUIRED, 'Directory name to create Bolt install in.'),
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
        $siteDir = $input->getArgument('dir');

        $output->writeln('<comment>Starting creation process…</comment>');

        // Turn on sudo early
        $this->doActivateSudo();

        // Create the Composer project
        $this->doCreateProject($siteDir, $config, $output);
    }

    protected function doCreateProject($siteDir, Config $config, OutputInterface $output)
    {
        $siteName = basename($siteDir);
        $updateSource = new Action\CreateProject($siteName, $config, $output);
        try {
            $updateSource->setSiteDir($siteDir);
            $updateSource->execute();
            $output->writeln(sprintf('<info>Successfully created Composer project.</info>', $siteName));
        } catch (\Exception $e) {
            $output->writeln('<error>Failed to create Composer project!</error>');
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
            die();
        }
    }
}
