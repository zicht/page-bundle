<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\PageBundle\DependencyInjection;
 
use Zicht\Bundle\PageBundle\DependencyInjection\ZichtPageExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ZichtPageExtensionTest extends \PHPUnit_Framework_TestCase
{
    protected function createContainer()
    {
        return new ContainerBuilder();
    }

    function testLoad()
    {
        $ext = new ZichtPageExtension();
        $container = $this->createContainer();

        $config = array(
            'pageClass' => 'My\Page',
            'contentItemClass' => 'My\ContentItem',
            'types' => array(
                'page' => array('Foo', 'Bar'),
                'contentItem' => array('Baz', 'Bat')
            ),

            'aliasing' => true
        );
        $container->setParameter('twig.form.resources', array());
        $ext->load(array($config), $container);

        $classMap = array(
            'zicht_page.page_url_provider'                        => '%zicht_page.page_url_provider.class%',
            'zicht_page.page_aliaser'                             => 'Zicht\Bundle\UrlBundle\Aliasing\Aliaser',
            'zicht_page.page_manager'                             => '%zicht_page.page_manager.class%',
            'zicht_page.page_manager_subscriber'                  => '%zicht_page.page_manager_subscriber.class%',
            'zicht_page.form.type.zicht_content_item_type_type'   => '%zicht_page.form.type.zicht_content_item_type_type.class%',
            'zicht_page.form.type.zicht_content_item_region_type' => '%zicht_page.form.type.zicht_content_item_region_type.class%',
            'zicht_page.admin.event_propagation_builder'          => '%zicht_page.admin.event_propagation_builder.class%'
        );

        foreach ($classMap as $key => $className) {
            $this->assertTrue($container->hasDefinition($key), "Testing if the definition for {$key} exists");
            $this->assertEquals($container->getDefinition($key)->getClass(), $className);
        }

        $this->assertEquals(array('@ZichtPage/form_theme.html.twig'), $container->getParameter('twig.form.resources'));
    }
}