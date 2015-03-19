<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\DependencyInjection;

use \Symfony\Component\Config\Definition\Builder\TreeBuilder;
use \Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Page bundle configuration
 */
class Configuration implements ConfigurationInterface
{
    /**
     * @{inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('zicht_page');

        $rootNode
            ->children()
                ->booleanNode('aliasing')->defaultValue(true)->end()
                ->arrayNode('types')
                    ->isRequired()
                    ->children()
                        ->arrayNode('page')->prototype('scalar')->isRequired()->end()->end()
                        ->arrayNode('contentItem')->prototype('scalar')->isRequired()->end()->end()
                    ->end()
                ->end()
                ->scalarNode('pageClass')->isRequired()->end()
                ->scalarNode('contentItemClass')->isRequired()->end()
                ->arrayNode('admin')
                    ->children()
                        ->arrayNode('base')
                            ->children()
                                ->scalarNode('page')->end()
                                ->scalarNode('contentItem')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('defaultRegions')->prototype('scalar')->isRequired()->end()->end()
                // localePrefixes used in LanguageAwareAliasingStrategy
                ->arrayNode('localePrefixes')->prototype('scalar')->end()->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
