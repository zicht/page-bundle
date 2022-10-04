<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\AdminMenu;

use PHPUnit\Framework\TestCase;
use Zicht\Bundle\PageBundle\Command\AliasCommand;

class AliasCommandTest extends TestCase
{
    public function setUp(): void
    {
        $this->markTestSkipped('Requires a lot of work for a Command we barely use...');
        $this->command = new AliasCommand();
        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')->getMock();
        $this->command->setContainer($this->container);
    }

    public function testConfig()
    {
        $this->markTestSkipped('Requires a lot of work for a Command we barely use...');
        $this->assertGreaterThan(0, strlen($this->command->getDescription()));
        $this->assertGreaterThan(0, strlen($this->command->getName()));
        $this->assertGreaterThan(0, strlen($this->command->getName()));
    }

    public function testExecute()
    {
        $this->markTestSkipped('Requires a lot of work for a Command we barely use...');
//        $aliaser = $this->getMockBuilder('Zicht\Bundle\UrlBundle\Aliasing\Aliaser')->disableOriginalConstructor()->getMock();
//        $isCalled = false;testViewActionFindsPageForViewAndForwardsIfItIsaControllerPag
//        $aliaser->expects($this->once())->method('setIsBatch')->with(true)->will($this->returnValue(function () use (&$isCalled) {
//            $isCalled = true;
//        }));
//        $pageManager = $this->getMockBuilder('Zicht\Bundle\PageBundle\Manager\PageManager')->disableOriginalConstructor()->getMock();
//        $this->container->expects($this->at(0))->method('get')->with('zicht_page.page_aliaser')->will($this->returnValue(
//            $aliaser
//        ));
//        $this->container->expects($this->at(1))->method('get')->with('zicht_page.page_manager')->will($this->returnValue(
//            $pageManager
//        ));
//
//        $items = array('a', 'b', 'c');
//        $pageManager->expects($this->once())->method('findAll')->will($this->returnValue($items));
//        $aliaser->expects($this->exactly(count($items)))->method('createAlias');
//
//        $this->command->run(
//            $this->getMock('Symfony\Component\Console\Input\InputInterface'),
//            $this->getMock('Symfony\Component\Console\Output\OutputInterface')
//        );
//
//        $this->assertTrue($isCalled);
    }
}
