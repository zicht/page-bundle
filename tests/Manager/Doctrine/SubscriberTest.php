<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace {
    class foo
    {
    }
}

namespace ZichtTest\Bundle\PageBundle\Manager\Doctrine {
    use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
    use PHPUnit\Framework\TestCase;
    use Symfony\Component\DependencyInjection\ContainerInterface;
    use Zicht\Bundle\PageBundle\Manager\PageManager;

    class SubscriberTest extends TestCase
    {
        public function testEvent()
        {
            $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
            $pm = $this->getMockBuilder(PageManager::class)->disableOriginalConstructor()
                ->setMethods(['decorateClassMetaData'])
                ->getMock();
            $container->expects($this->any())->method('get')->with('zicht_page.page_manager')->will(
                $this->returnValue(
                    $pm
                )
            );

            $s = new \Zicht\Bundle\PageBundle\Manager\Doctrine\Subscriber($pm);
            $this->assertEquals(['loadClassMetadata'], $s->getSubscribedEvents());
            $event = $this->getMockBuilder(LoadClassMetadataEventArgs::class)->disableOriginalConstructor()->getMock();
            $metadata = new \Doctrine\ORM\Mapping\ClassMetadata('\foo');
            $event->expects($this->once())->method('getClassMetadata')->will($this->returnValue($metadata));
            $pm->expects($this->once())->method('decorateClassMetaData')->with($metadata);

            $s->loadClassMetaData($event);
        }
    }
}
