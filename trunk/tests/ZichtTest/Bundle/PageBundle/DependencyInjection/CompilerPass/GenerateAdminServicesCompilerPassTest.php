<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\DependencyInjection\CompilerPass;

class GenerateAdminServicesCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    public function testBareConfig()
    {
        $c = new \Symfony\Component\DependencyInjection\ContainerBuilder();
        $c->setParameter('zicht_page.config', array());
        $pass = new \Zicht\Bundle\PageBundle\DependencyInjection\CompilerPass\GenerateAdminServicesCompilerPass();
        $pass->process($c);
    }


    public function testValidConfig()
    {
        $c = new \Symfony\Component\DependencyInjection\ContainerBuilder();

        $defs = array(
            'zicht_page.page_manager' => new \Symfony\Component\DependencyInjection\Definition(),
            'page.admin' => new \Symfony\Component\DependencyInjection\Definition(),
            'ci.admin' => new \Symfony\Component\DependencyInjection\Definition(),
        );

        $defs['zicht_page.page_manager']->addMethodCall('setPageTypes', array(array('a', 'b', 'c')));
        $defs['zicht_page.page_manager']->addMethodCall('setContentItemTypes', array(array('d', 'e', 'f')));

        foreach ($defs as $id => $def) {
            $c->setDefinition($id, $def);
        }
        $c->setParameter('zicht_page.config', array(
            'admin' => array(
                'base' => array(
                    'page' => 'page.admin',
                    'contentItem' => 'ci.admin'
                )
            )
        ));
        $pass = new \Zicht\Bundle\PageBundle\DependencyInjection\CompilerPass\GenerateAdminServicesCompilerPass();
        $pass->process($c);
    }
}