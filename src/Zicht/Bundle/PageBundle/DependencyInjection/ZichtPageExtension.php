<?php
/**
 * @author Jeroen Fiege <jeroenf@zicht.nl>
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\DependencyInjection;

use \Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use \Symfony\Component\DependencyInjection\Reference;
use \Symfony\Component\Config\FileLocator;
use \Symfony\Component\HttpKernel\DependencyInjection\Extension;
use \Symfony\Component\DependencyInjection\Loader;

/**
 * Page bundle DI extension
 */
class ZichtPageExtension extends Extension
{
    /**
     * @{inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (true === $config['aliasing']['enabled']) {
            $loader->load('aliasing.xml');

            if (!empty($config['aliasing']['prefixLanguages'])) {
                $def = new Definition(
                    'Zicht\Bundle\PageBundle\Aliasing\Strategy\LanguageAwareAliasingStrategy', [
                        new Reference($config['aliasing']['service']),
                        $config['aliasing']['prefixLanguages']
                    ]
                );
            } else {
                $def = new Reference($config['aliasing']['service']);
            }
            $container->getDefinition('zicht_page.page_aliaser')->replaceArgument(2, $def);
        }

        $container
            ->getDefinition('zicht_page.form.type.zicht_content_item_region_type')
            ->replaceArgument(0, $config['contentItemClass'])
            ->replaceArgument(1, $config['defaultRegions'])
        ;

        $container
            ->getDefinition('zicht_page.form.type.zicht_content_item_type_type')
            ->replaceArgument(1, $config['contentItemClass']);

        $def = $container->getDefinition('zicht_page.page_manager');
        $def->replaceArgument(2, $config['pageClass']);
        $def->replaceArgument(3, $config['contentItemClass']);

        $def->addMethodCall('setPageTypes', array($config['types']['page']));
        $def->addMethodCall('setContentItemTypes', array($config['types']['contentItem']));
        $container->setParameter('zicht_page.config', $config);
        $container->setParameter('zicht_page.page_types', $config['types']['page']);

        if ($container->hasParameter('twig.form.resources')) {
            $formResources = $container->getParameter('twig.form.resources');
            $formResources[]= 'ZichtPageBundle::form_theme.html.twig';
            $container->setParameter('twig.form.resources', $formResources);
        }
    }
}
