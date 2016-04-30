<?php

namespace Bolt\Deploy\Command;

use Bolt\Deploy\Config\Config;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base command class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class BaseCommand extends Command
{
    /**
     * Load configuration files.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return Config
     */
    protected function loadConfiguration(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('config') !== null) {
            $configFile = $input->getOption('config');
        } elseif (file_exists(getcwd() . '/.deploy.yml')) {
            $configFile = getcwd() . '/.deploy.yml';
        } elseif (file_exists('/etc/deploy.yml')) {
            $configFile = '/etc/deploy.yml';
        } else {
            $configFile = getenv('HOME') . '/.deploy.yml';
        }

        try {
            return new Config($configFile);
        } catch (FileLoaderLoadException $e) {
            $output->writeln(sprintf('<error>%s</error>', stripslashes($e->getMessage())));
            die();
        } catch (InvalidConfigurationException $e) {
            $output->writeln(sprintf('<error>%s</error>', stripslashes($e->getMessage())));
            die();
        }
    }
}
