<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\Controller;

class AbstractControllerTest extends \PHPUnit_Framework_TestCase
{
    function testGetPagemanagerReturnsPageManagerService()
    {
        $foo = rand(1, 100);

        $controller = $this->getMock('Zicht\Bundle\PageBundle\Controller\AbstractController', array('get'));
        $controller
            ->expects($this->once())->method('get')
            ->with('zicht_page.page_manager')
            ->will($this->returnValue($foo))
        ;
        $this->assertEquals($foo, $controller->getPageManager());
    }
}