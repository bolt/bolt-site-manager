<?php

namespace Bolt\Deploy\Config;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Configuration tree.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class ConfigurationTree implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root('root');

        $rootNode
            ->children()
                ->arrayNode('paths')
                    ->ignoreExtraKeys(true)
                    ->children()
                        ->scalarNode('git')
                            ->defaultValue('/usr/bin/git')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('sites')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('path')->end()
                            ->booleanNode('backup')
                                ->info('When true, backup the site prior to applying the deployment changes.')
                                ->defaultTrue()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
