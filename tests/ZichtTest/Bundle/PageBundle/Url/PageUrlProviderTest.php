<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace ZichtTest\Bundle\PageBundle\Url {
class PageUrlProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $this->createUrlProvider();
    }

    protected function createUrlProvider()
    {
        $this->pageManager = $this->getMockBuilder('Zicht\Bundle\PageBundle\Manager\PageManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getPageClass', 'getBaseRepository'))
            ->getMock()
        ;

        $this->pageManager->expects($this->any())->method('getPageClass')->will($this->returnValue('Foo\Bar\Page'));

        return new \Zicht\Bundle\PageBundle\Url\PageUrlProvider(
            $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock(),
            $this->pageManager
        );
    }


    /**
     * @depends testConstruct
     */
    public function testSupports()
    {
        $provider = $this->createUrlProvider();
        $this->assertTrue($provider->supports(new \Foo\Bar\Page()));
        $this->assertTrue($provider->supports(new \Foo\Bar\SubPage()));
        $this->assertFalse($provider->supports(new \Foo\Bar\Baz()));
    }

    /**
     * @depends testConstruct
     */
    public function testRouting()
    {
        $provider = $this->createUrlProvider();
        $page = $this->getMock('Foo\Bar\Page');

        $rand = rand(1, 100);
        $page->expects($this->once())->method('getId')->will($this->returnValue($rand));
        $this->assertEquals(
            array(
                'zicht_page_page_view',
                array(
                    'id' => $rand
                )
            ),
            $provider->routing($page)
        );
    }


    public function testSuggest()
    {
        $p = $this->createUrlProvider();
        $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->setMethods(array('createQueryBuilder'))
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->pageManager->expects($this->once())->method('getBaseRepository')
            ->will($this->returnValue($repo))
        ;
        $q = $this->getMockBuilder('stdClass')->setMethods(array('execute'))
            ->disableOriginalConstructor()->getMock();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();

        $qb->expects($this->once())->method('setMaxResults')->with(30)->will($this->returnValue($qb));

        $repo->expects($this->once())->method('createQueryBuilder')->will($this->returnValue($qb));
        $qb->expects($this->any())->method('andWhere')->will($this->returnValue($qb));
        $qb->expects($this->any())->method('getQuery')->will($this->returnValue($q));

        $q->expects($this->any())->method('execute')->will($this->returnValue(array(
            new \Foo\Bar\Page(),
            new \Foo\Bar\Page(),
        )));

        $items = $p->suggest('foo');
        $this->assertCount(2, $items);
        foreach ($items as $item) {
            $this->assertTrue(array_key_exists('value', $item));
            $this->assertTrue(array_key_exists('label', $item));
        }
    }
}
}

namespace Foo\Bar {
    class Baz {}
    class Page implements \Zicht\Bundle\PageBundle\Model\PageInterface {
        public function getId() {}
        public function getTitle() {}
        public function getContentItemMatrix() {}
        public function getTemplateName() {}
        public function getDisplayType() {}
    }
    class SubPage extends Page {}
}