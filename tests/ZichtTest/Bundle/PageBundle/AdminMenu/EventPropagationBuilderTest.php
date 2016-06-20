<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\PageBundle\AdminMenu;
 
use Zicht\Bundle\PageBundle\AdminMenu\EventPropagationBuilder;
use Zicht\Bundle\PageBundle\Model\ContentItemInterface;
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

class EventPropagationBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventPropagationBuilder
     */
    protected $propagator;
    protected $pool;

    function setUp()
    {
        $this->pool = $this->getMockBuilder('Sonata\AdminBundle\Admin\Pool')->disableOriginalConstructor()->getMock();
        $this->propagator = new EventPropagationBuilder($this->pool);
    }

    function testFiringPageViewEventWillCheckClassHierarchyForAdminClass()
    {
        $classes = array();
        $this->pool
            ->expects($this->any())
            ->method('getAdminByClass')
            ->will(
                $this->returnCallback(function($c) use(&$classes){
                    $classes[] = $c;
                })
            );
        $event = new \Zicht\Bundle\PageBundle\Event\PageViewEvent(new P1('bar'));
        $this->propagator->buildAndForwardEvent($event);

        $this->assertEquals(
            $classes,
            array(
                'ZichtTest\Bundle\PageBundle\AdminMenu\P1',
                'ZichtTest\Bundle\PageBundle\Assets\PageAdapter'
            )
        );
    }

    function testFiringForeignEventDoesNotFail()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
        $this->pool->expects($this->any())->method('getAdminByClass')->will($this->returnValue(null));
        $event = new \Symfony\Component\EventDispatcher\Event();
        $event->setDispatcher($dispatcher);
        $dispatcher->expects($this->never())->method('dispatch');
        $this->propagator->buildAndForwardEvent($event);
    }


    function testFiringPageViewEventWillNotFirEventIfNoAdminIsFound()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
        $this->pool->expects($this->any())->method('getAdminByClass')->will($this->returnValue(null));
        $event = new \Zicht\Bundle\PageBundle\Event\PageViewEvent(new P1("bar"));
        $event->setDispatcher($dispatcher);
        $dispatcher->expects($this->never())->method('dispatch');
        $this->propagator->buildAndForwardEvent($event);
    }

    function testFiringPageViewEventWillFirEventIfAdminIsAvailable()
    {
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
        $admin = $this->getMockBuilder('Sonata\AdminBundle\Admin\Admin')->disableOriginalConstructor()->getMock();
        $this->pool->expects($this->once())->method('getAdminByClass')->with('ZichtTest\Bundle\PageBundle\AdminMenu\P1')->will($this->returnValue($admin));
        $page = new P1('bar');
        $event = new \Zicht\Bundle\PageBundle\Event\PageViewEvent($page);
        $url = '/foo';
        $admin->expects($this->once())->method('generateObjectUrl')->with('edit', $page)->will($this->returnVAlue($url));
        $event->setDispatcher($dispatcher);
        $dispatcher->expects($this->once())->method('dispatch');
        $this->propagator->buildAndForwardEvent($event);
    }
}
