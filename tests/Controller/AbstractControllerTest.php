<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\Controller;

use PHPUnit\Framework\TestCase;

class AbstractControllerTest extends TestCase
{
    public function testGetPagemanagerReturnsPageManagerService()
    {
        $foo = rand(1, 100);

        $controller = $this->getMockBuilder('Zicht\Bundle\PageBundle\Controller\AbstractController')
            ->setMethods(['get'])->getMock();
        $controller
            ->expects($this->once())->method('get')
            ->with('zicht_page.page_manager')
            ->will($this->returnValue($foo));
        $this->assertEquals($foo, $controller->getPageManager());
    }
}
