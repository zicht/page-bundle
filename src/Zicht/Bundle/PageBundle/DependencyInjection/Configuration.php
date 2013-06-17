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
                ->arrayNode('types')
                    ->isRequired()
                    ->children()
                        ->arrayNode('page')->prototype('scalar')->isRequired()->end()->end()
                        ->arrayNode('contentItem')->prototype('scalar')->isRequired()->end()->end()
                    ->end()
                ->end()
                ->scalarNode('pageClass')->isRequired()->end()
                ->scalarNode('contentItemClass')->isRequired()->end()
                ->arrayNode('defaultRegions')->prototype('scalar')->isRequired()->end()->end()
            ->end()
        ;
        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
