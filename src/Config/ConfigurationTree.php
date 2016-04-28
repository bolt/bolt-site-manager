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
    /**
     * {@inheritdoc}
     */
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
            ->end()
            ->append($this->addSitesNode())
        ;

        return $treeBuilder;
    }

    /**
     * Sites configuration node.
     *
     * @return ArrayNodeDefinition
     */
    protected function addSitesNode()
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $node */
        $node = $builder->root('sites');

        $node
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('path')->end()
                        ->booleanNode('backup')
                        ->info('When true, backup the site prior to applying the deployment changes.')
                        ->defaultTrue()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
