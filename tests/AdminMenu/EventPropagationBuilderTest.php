<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\AdminMenu;

use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\EventDispatcher\Event;
use Zicht\Bundle\PageBundle\AdminMenu\EventPropagationBuilder;
use Zicht\Bundle\PageBundle\Event\PageViewEvent;
use ZichtTest\Bundle\PageBundle\Assets\PageAdapter;

class P1 extends PageAdapter
{
    public function __construct($title)
    {
        parent::__construct();
        $this->title = $title;
    }

    /**
     * A page must always have an id
     *
     * @return mixed
     */
    public function getId()
    {
        return rand(1, 100);
    }

    /**
     * A page must always have a title.
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
}

class EventPropagationBuilderTest extends TestCase
{
    /**
     * @var EventPropagationBuilder
     */
    protected $propagator;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var EventDispatcher
     */
    protected $dispatcher;

    public function setUp(): void
    {
        $this->markTestSkipped('Mark skipped until resolving mocking final class Pool');
        // $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
        // $this->dispatcher = self::getMockBuilder(EventDispatcher::class)->disableOriginalConstructor()->getMock();
        // $this->propagator = new EventPropagationBuilder($this->pool, null, $this->dispatcher);
    }

    public function testFiringPageViewEventWillCheckClassHierarchyForAdminClass()
    {
        $classes = [];
        $this->pool
            ->expects($this->any())
            ->method('getAdminByClass')
            ->will(
                $this->returnCallback(function ($c) use (&$classes) {
                    $classes[] = $c;
                })
            );

        $event = new PageViewEvent(new P1('bar'));
        $this->propagator->buildAndForwardEvent($event);

        $this->assertEquals($classes, [P1::class, PageAdapter::class]);
    }

    public function testFiringForeignEventDoesNotFail()
    {
        $this->pool->expects($this->any())->method('getAdminByClass')->will($this->returnValue(null));
        $event = new Event();

        $this->dispatcher->expects($this->never())->method('dispatch');
        $this->propagator->buildAndForwardEvent($event);
    }

    public function testFiringPageViewEventWillNotFirEventIfNoAdminIsFound()
    {
        $this->pool->expects($this->any())->method('getAdminByClass')->will($this->returnValue(null));
        $event = new PageViewEvent(new P1('bar'));

        $this->dispatcher->expects($this->never())->method('dispatch');
        $this->propagator->buildAndForwardEvent($event);
    }

    public function testFiringPageViewEventWillFirEventIfAdminIsAvailable()
    {
        $admin = $this->getMockBuilder(AdminInterface::class)->disableOriginalConstructor()->getMock();
        $this->pool->expects($this->once())->method('getAdminByClass')->with(P1::class)->will($this->returnValue($admin));
        $page = new P1('bar');
        $event = new PageViewEvent($page);
        $url = '/foo';
        $admin->expects($this->once())->method('generateObjectUrl')->with('edit', $page)->will($this->returnVAlue($url));

        $this->dispatcher->expects($this->once())->method('dispatch');
        $this->propagator->buildAndForwardEvent($event);
    }
}
