<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\Url {
    use PHPUnit\Framework\TestCase;

    class PageUrlProviderTest extends TestCase
    {
        public function setUp(): void
        {
            $this->createUrlProvider();
        }

        protected function createUrlProvider()
        {
            $this->pageManager = $this->getMockBuilder('Zicht\Bundle\PageBundle\Manager\PageManager')
            ->disableOriginalConstructor()
            ->setMethods(['getPageClass', 'getBaseRepository'])
            ->getMock();

            $this->pageManager->expects($this->any())->method('getPageClass')->will($this->returnValue('Foo\Bar\Page'));

            $this->urlProvider = new \Zicht\Bundle\PageBundle\Url\PageUrlProvider(
                $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock(),
                $this->pageManager
            );
        }

        public function testSupports()
        {
            $this->assertTrue($this->urlProvider->supports(new \Foo\Bar\Page()));
            $this->assertTrue($this->urlProvider->supports(new \Foo\Bar\SubPage()));
            $this->assertFalse($this->urlProvider->supports(new \Foo\Bar\Baz()));
        }

        public function testRouting()
        {
            $page = $this->createMock('Foo\Bar\Page');

            $rand = rand(1, 100);
            $page->expects($this->once())->method('getId')->will($this->returnValue($rand));
            $this->assertEquals(
                [
                    'zicht_page_page_view',
                    [
                        'id' => $rand,
                    ],
                ],
                $this->urlProvider->routing($page)
            );
        }

        public function testSuggest()
        {
            $repo = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->setMethods(['createQueryBuilder'])
            ->disableOriginalConstructor()
            ->getMock();
            $this->pageManager->expects($this->once())->method('getBaseRepository')
            ->will($this->returnValue($repo));
            $q = $this->getMockBuilder('stdClass')->setMethods(['execute'])
            ->disableOriginalConstructor()->getMock();
            $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')->disableOriginalConstructor()->getMock();

            $qb->expects($this->once())->method('setMaxResults')->with(30)->will($this->returnValue($qb));

            $repo->expects($this->once())->method('createQueryBuilder')->will($this->returnValue($qb));
            $qb->expects($this->any())->method('andWhere')->will($this->returnValue($qb));
            $qb->expects($this->any())->method('getQuery')->will($this->returnValue($q));

            $q->expects($this->any())->method('execute')->will(
                $this->returnValue([
                    new \Foo\Bar\Page(),
                    new \Foo\Bar\Page(),
                ])
            );

            $items = $this->urlProvider->suggest('foo');
            $this->assertCount(2, $items);
            foreach ($items as $item) {
                $this->assertTrue(array_key_exists('value', $item));
                $this->assertTrue(array_key_exists('label', $item));
            }
        }
    }
}

namespace Foo\Bar {
    use Zicht\Bundle\PageBundle\Model\ContentItemInterface;

    class Baz
    {
    }
    class Page implements \Zicht\Bundle\PageBundle\Model\PageInterface
    {
        public function getId()
        {
            return 1;
        }

        public function getTitle()
        {
        }

        public function getContentItemMatrix()
        {
        }

        public function getTemplateName()
        {
        }

        public function getDisplayType()
        {
        }

        public function getContentItems($region = null)
        {
        }

        public function addContentItem(ContentItemInterface $contentItem)
        {
        }

        public function removeContentItem(ContentItemInterface $contentItem)
        {
        }

        public function isPublic()
        {
        }
    }
    class SubPage extends Page
    {
    }
}
