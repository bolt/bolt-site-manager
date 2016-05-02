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
            ->append($this->addBinariesNode())
            ->append($this->addPermissionsNode())
            ->append($this->addAclNode())
            ->append($this->addSitesNode())
        ;

        return $treeBuilder;
    }

    /**
     * File system permission configuration node.
     *
     * @return ArrayNodeDefinition
     */
    protected function addBinariesNode()
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $node */
        $node = $builder->root('binaries');
        $pathCheck = function ($path) {
            return !realpath($path);
        };

        $node
            ->ignoreExtraKeys(true)
            ->children()
                ->scalarNode('git')
                    ->defaultValue('/usr/bin/git')
                    ->isRequired()
                    ->validate()
                    ->ifTrue($pathCheck)
                        ->thenInvalid('Could not find git binary at %s.')
                    ->end()
                ->end()
                ->scalarNode('rsync')
                    ->defaultValue('/usr/bin/rsync')
                    ->isRequired()
                    ->validate()
                    ->ifTrue($pathCheck)
                        ->thenInvalid('Could not find rsync binary at %s.')
                    ->end()
                ->end()
                ->scalarNode('setfacl')
                    ->defaultValue('/usr/bin/setfacl')
                    ->isRequired()
                    ->validate()
                    ->ifTrue($pathCheck)
                        ->thenInvalid('Could not find setfacl binary at %s.')
                    ->end()
                ->end()
                ->scalarNode('mysqldump')
                    ->defaultValue('/usr/bin/mysqldump')
                    ->validate()
                    ->ifTrue($pathCheck)
                        ->thenInvalid('Could not find mysqldump binary at %s.')
                    ->end()
                ->end()
                ->scalarNode('pg_dump')
                    ->defaultValue('/usr/bin/pg_dump')
                    ->validate()
                    ->ifTrue($pathCheck)
                        ->thenInvalid('Could not find pg_dump binary at %s.')
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * File system permission configuration node.
     *
     * @return ArrayNodeDefinition
     */
    protected function addPermissionsNode()
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $node */
        $node = $builder->root('permissions');

        $node
            ->children()
                ->scalarNode('user')
                    ->isRequired()
                ->end()
                ->scalarNode('group')
                    ->isRequired()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * ACL configuration node.
     *
     * @return ArrayNodeDefinition
     */
    protected function addAclNode()
    {
        $builder = new TreeBuilder();
        /** @var ArrayNodeDefinition $node */
        $node = $builder->root('acls');

        $node
            ->children()
                ->arrayNode('users')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('groups')
                    ->prototype('scalar')->end()
                ->end()
            ->end()
        ;

        return $node;
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
        $pathCheck = function ($path) {
            return !realpath($path);
        };

        $node
            ->isRequired()
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->arrayNode('paths')
                        ->children()
                            ->scalarNode('site')
                                ->isRequired()
                                ->validate()
                                ->ifTrue($pathCheck)
                                    ->thenInvalid('Could not find site directory at %s.')
                                ->end()
                            ->end()
                            ->scalarNode('source')
                                ->isRequired()
                                ->validate()
                                ->ifTrue($pathCheck)
                                    ->thenInvalid('Could not find source directory at %s.')
                                ->end()
                            ->end()
                            ->scalarNode('backup')
                                ->isRequired()
                                ->validate()
                                ->ifTrue($pathCheck)
                                    ->thenInvalid('Could not find backup directory at %s.')
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('backup')
                        ->children()
                            ->booleanNode('timestamp')
                                ->info('Save the backup in a timestamped directory')
                                ->isRequired()
                                ->defaultTrue()
                            ->end()
                            ->arrayNode('files')
                                ->children()
                                    ->booleanNode('enabled')
                                        ->info('Backup the site prior to applying the deployment changes.')
                                        ->isRequired()
                                        ->defaultTrue()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('database')
                                ->children()
                                    ->booleanNode('enabled')
                                        ->info('Backup the database prior to applying the deployment changes.')
                                        ->isRequired()
                                        ->defaultTrue()
                                    ->end()
                                    ->scalarNode('auth_file')
                                        ->info('YAML file containing database credentials')
                                        ->isRequired()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                    ->arrayNode('exclude')
                        ->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
