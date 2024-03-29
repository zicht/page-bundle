<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('zicht_page');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('aliasing')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('service')->defaultValue('zicht_page.page_aliasing_strategy')->end()
                        ->arrayNode('prefixLanguages')->prototype('scalar')->end()->end()
                        ->scalarNode('conflictingInternalUrlStrategy')
                            ->defaultValue('ignore')
                            ->validate()
                            ->ifNotInArray(['ignore', 'redirect-previous-to-new'])
                                ->thenInvalid('Invalid conflictingInternalUrlStrategy')
                            ->end()
                        ->end()
                        ->scalarNode('conflictingPublicUrlStrategy')
                            ->defaultValue('suffix')
                            ->validate()
                            ->ifNotInArray(['keep', 'overwrite', 'suffix'])
                                ->thenInvalid('Invalid conflictingPublicUrlStrategy')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('types')
                    ->isRequired()
                    ->children()
                        ->arrayNode('page')->prototype('scalar')->isRequired()->end()->end()
                        ->arrayNode('contentItem')->prototype('scalar')->isRequired()->end()->end()
                    ->end()
                ->end()
                ->scalarNode('pageClass')->isRequired()->end()
                ->scalarNode('contentItemClass')->isRequired()->end()
                ->scalarNode('contentItemPageProperty')->defaultValue('page')->end()
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
            ->end();

        return $treeBuilder;
    }
}
