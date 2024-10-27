<?php

namespace Elenyum\Authorization\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('elenyum_authorization');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('cache')
                    ->children()
                        ->booleanNode('enable')
                            ->info('define cache enable for authorization')
                            ->defaultFalse()
                        ->end()
                        ->scalarNode('item_id')
                            ->info('define cache item id')
                            ->defaultValue('elenyum_authorization')
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
