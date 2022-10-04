<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Zicht\Bundle\PageBundle\DependencyInjection\ZichtPageExtension;

class ZichtPageExtensionTest extends TestCase
{
    protected function createContainer()
    {
        return new ContainerBuilder();
    }

    public function testLoad()
    {
        $ext = new ZichtPageExtension();
        $container = $this->createContainer();

        $config = [
            'pageClass' => 'My\Page',
            'contentItemClass' => 'My\ContentItem',
            'types' => [
                'page' => ['Foo', 'Bar'],
                'contentItem' => ['Baz', 'Bat'],
            ],

            'aliasing' => true,
        ];
        $container->setParameter('twig.form.resources', []);
        $ext->load([$config], $container);

        $classMap = [
            'zicht_page.page_url_provider' => '%zicht_page.page_url_provider.class%',
            'zicht_page.page_aliaser' => 'Zicht\Bundle\UrlBundle\Aliasing\Aliaser',
            'zicht_page.page_manager' => '%zicht_page.page_manager.class%',
            'zicht_page.page_manager_subscriber' => '%zicht_page.page_manager_subscriber.class%',
            'zicht_page.form.type.zicht_content_item_type_type' => '%zicht_page.form.type.zicht_content_item_type_type.class%',
            'zicht_page.form.type.zicht_content_item_region_type' => '%zicht_page.form.type.zicht_content_item_region_type.class%',
            'zicht_page.admin.event_propagation_builder' => '%zicht_page.admin.event_propagation_builder.class%',
        ];

        foreach ($classMap as $key => $className) {
            $this->assertTrue($container->hasDefinition($key), "Testing if the definition for {$key} exists");
            $this->assertEquals($container->getDefinition($key)->getClass(), $className);
        }

        $this->assertEquals(['@ZichtPage/form_theme.html.twig'], $container->getParameter('twig.form.resources'));
    }
}
