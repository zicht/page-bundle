<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
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
            )
        );
        $container->setParameter('twig.form.resources', array());
        $ext->load(array($config), $container);

        $classMap = array(
            'zicht_page.page_url_provider'                        => 'Zicht\Bundle\PageBundle\Url\PageUrlProvider',
            'zicht_page.page_aliaser'                             => 'Zicht\Bundle\UrlBundle\Aliasing\Aliaser',
            'zicht_page.page_manager'                             => 'Zicht\Bundle\PageBundle\Manager\PageManager',
            'zicht_page.page_manager_subscriber'                  => 'Zicht\Bundle\PageBundle\Manager\Doctrine\Subscriber',
            'zicht_page.form.type.zicht_content_item_type_type'   => 'Zicht\Bundle\PageBundle\Type\ContentItemTypeType',
            'zicht_page.form.type.zicht_content_item_region_type' => 'Zicht\Bundle\PageBundle\Type\ContentItemRegionType',
            'zicht_page.admin.event_propagation_builder'          => 'Zicht\Bundle\PageBundle\AdminMenu\EventPropagationBuilder'
        );

        foreach ($classMap as $key => $className) {
            $this->assertTrue($container->hasDefinition($key));
            $this->assertTrue($container->getDefinition($key)->getClass() == $className);
        }

        $this->assertEquals(array('ZichtPageBundle::form_theme.html.twig'), $container->getParameter('twig.form.resources'));
    }
}