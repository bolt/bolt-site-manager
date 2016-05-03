<?php

namespace Bolt\Deploy\Command;

use Bolt\Deploy\Config\Config;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Base command class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
abstract class AbstractCommand extends Command
{
    /** @var string */
    protected $configFile;
    
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
        if ($input->hasOption('config') && $input->getOption('config') !== null) {
            $this->configFile = $input->getOption('config');
        } elseif (file_exists(getcwd() . '/.site-deploy.yml')) {
            $this->configFile = getcwd() . '/.site-deploy.yml';
        } elseif (file_exists('/etc/site-deploy.yml')) {
            $this->configFile = '/etc/site-deploy.yml';
        } else {
            $this->configFile = getenv('HOME') . '/.site-deploy.yml';
        }

        $fs = new Filesystem();
        if (!$fs->exists($this->configFile)) {
            $output->writeln('<error>Unable to find valid configuration file.</error>');
            die();
        }

        try {
            return new Config($this->configFile);
        } catch (FileLoaderLoadException $e) {
            $output->writeln(sprintf('<error>%s</error>', stripslashes($e->getMessage())));
            die();
        } catch (InvalidConfigurationException $e) {
            $output->writeln(sprintf('<error>%s</error>', stripslashes($e->getMessage())));
            die();
        }
    }
}
