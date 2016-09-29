<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Zicht\Bundle\UrlBundle\Aliasing\Aliasing;

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
                ->arrayNode('aliasing')
                    ->canBeEnabled()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('service')->defaultValue('zicht_page.page_aliasing_strategy')->end()
                        ->arrayNode('prefixLanguages')->prototype('scalar')->end()->end()
                        ->scalarNode('conflictingInternalUrlStrategy')
                            ->defaultValue(Aliasing::STRATEGY_IGNORE)
                            ->validate()
                            ->ifNotInArray([Aliasing::STRATEGY_IGNORE, Aliasing::STRATEGY_MOVE_PREVIOUS_TO_NEW])
                                ->thenInvalid('Invalid conflictingInternalUrlStrategy')
                            ->end()
                        ->end()
                        ->scalarNode('conflictingPublicUrlStrategy')
                            ->defaultValue(Aliasing::STRATEGY_SUFFIX)
                            ->validate()
                            ->ifNotInArray([Aliasing::STRATEGY_KEEP, Aliasing::STRATEGY_OVERWRITE, Aliasing::STRATEGY_SUFFIX])
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
