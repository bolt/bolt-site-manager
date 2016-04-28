<?php

namespace Bolt\Deploy\Command;

use Bolt\Deploy\Config\Config;
use Bolt\Deploy\Config\ConfigurationTree;
use Bolt\Deploy\Config\YamlFileLoader;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Exception\FileLoaderLoadException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Console\Command\Command;
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
     * @param array|null $configDirectories
     *
     * @return Config
     */
    protected function loadConfiguration(OutputInterface $output, array $configDirectories = null)
    {
        if ($configDirectories === null) {
            $configDirectories = [getcwd()];
        }

        $locator = new FileLocator($configDirectories);
        $loaders = [
            new YamlFileLoader($locator)
        ];

        $loaderResolver = new LoaderResolver($loaders);
        $delegatingLoader = new DelegatingLoader($loaderResolver);

        $configuration = new ConfigurationTree();
        $processor = new Processor();

        try {
            $config = [
                'root' => $delegatingLoader->load($configDirectories[0] . '/config.yml')
            ];
        } catch (FileLoaderLoadException $e) {
            $output->writeln(sprintf('<error>%s</error>', stripslashes($e->getMessage())));
            die();
        }

        try {
            $processedConfiguration = $processor->processConfiguration(
                $configuration,
                $config
            );

            return new Config($processedConfiguration);
        } catch (InvalidConfigurationException $e) {
            $output->writeln(sprintf('<error>%s</error>', stripslashes($e->getMessage())));
            die();
        }
    }
}
