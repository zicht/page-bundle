<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\PageBundle\Manager\Doctrine;
 
use PHPUnit\Framework\TestCase;

class SubscriberTest extends TestCase
{
    function testEvent()
    {
        $container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock();
        $pm = $this->getMockBuilder('Zicht\Bundle\PageBundle\Manager\PageManager')->disableOriginalConstructor()
            ->setMethods(array('decorateClassMetaData'))
            ->getMock()
        ;
        $container->expects($this->any())->method('get')->with('zicht_page.page_manager')->will($this->returnValue(
            $pm
        ));

        $s = new \Zicht\Bundle\PageBundle\Manager\Doctrine\Subscriber($pm);
        $this->assertEquals(array('loadClassMetadata'), $s->getSubscribedEvents());
        $event = $this->getMockBuilder('Doctrine\ORM\Event\LoadClassMetadataEventArgs')->disableOriginalConstructor()->getMock();
        $metadata = new \Doctrine\ORM\Mapping\ClassMetadata('foo');
        $event->expects($this->once())->method('getClassMetadata')->will($this->returnValue($metadata));
        $pm->expects($this->once())->method('decorateClassMetaData')->with($metadata);

        $s->loadClassMetaData($event);
    }
}