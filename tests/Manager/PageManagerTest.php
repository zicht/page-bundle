<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\SomeBundle\Entity\Page {
    class SomePage
    {
    }

    class SomeOtherPage
    {
    }
}

namespace ZichtTest\Bundle\SomeBundle\Entity\ContentItem {
    class SomeContentItem
    {
    }

    class SomeOtherContentItem
    {
    }
}

namespace App\Entity {
    use ZichtTest\Bundle\PageBundle\Assets\PageAdapter;

    class FooBarPage extends PageAdapter
    {
        public function getTitle()
        {
            return '';
        }

        public function getId()
        {
            // TODO: Implement getId() method.
        }
    }
}

namespace My\PageBundle\Entity {
    use App\Entity\FooBarPage as AppFooBarPage;

    class FooBarPage extends AppFooBarPage
    {
    }
}

namespace ZichtTest\Bundle\PageBundle\Manager {
    use PHPUnit\Framework\TestCase;

    class PageManagerTest extends TestCase
    {
        protected $doctrine;

        protected $em;

        protected $repos;

        protected $eventDispatcher;

        protected $pageClassName;

        protected $contentItemClassName;

        protected $pageManager;

        protected $managerForClass;

        public function setUp(): void
        {
            $this->managerForClass = $this->createMock('Doctrine\Persistence\ObjectManager');
            $metadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadataInfo')->disableOriginalConstructor()->getMock();
            $metadata->discriminatorMap = ['bar' => 'My\PageBundle\Entity\FooBarPage'];
            $this->managerForClass->expects($this->any())->method('getClassMetadata')->willReturn($metadata);
            $this->doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')->disableOriginalConstructor()->getMock();
            $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->setMethods(
                ['getRepository', 'getClassMetaData']
            )->disableOriginalConstructor()->getMock();
            $this->repos = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->setMethods(['find', 'findOneBy', 'findAll'])->disableOriginalConstructor()->getMock();
            $this->em->expects($this->any())->method('getRepository')->will($this->returnValue($this->repos));
            $this->doctrine->expects($this->any())->method('getManager')->will($this->returnValue($this->em));
            $this->doctrine->expects($this->any())->method('getManagerForClass')->will($this->returnValue($this->managerForClass));
            $this->doctrine->expects($this->any())->method('getRepository')->will($this->returnValue($this->repos));
            $this->eventDispatcher = $this->createMock('Symfony\Component\EventDispatcher\EventDispatcher');
            $this->pageClassName = 'My\Page';
            $this->contentItemClassName = 'My\ContentItem';

            $this->pageManager = new \Zicht\Bundle\PageBundle\Manager\PageManager(
                $this->doctrine,
                $this->eventDispatcher,
                $this->pageClassName,
                $this->contentItemClassName
            );
        }

        public function testGetTemplateWillReturnAppTemplate()
        {
            $this->assertEquals(
                'page/foo-bar.html.twig',
                $this->pageManager->getTemplate(new \App\Entity\FooBarPage())
            );
        }

        public function testGetTemplateWillReturnBundleTemplate()
        {
            $this->assertEquals(
                '@MyPage/Page/foo-bar.html.twig',
                $this->pageManager->getTemplate(new \My\PageBundle\Entity\FooBarPage())
            );
        }

        public function testGetTemplateWillThrowExceptionIfBundleNameIsUndeterminable()
        {
            $this->expectException('\RuntimeException');
            $this->pageManager->getTemplate(new \stdClass());
        }

        public function testGetPageClassWillReturnPageClassName()
        {
            $this->assertEquals($this->pageClassName, $this->pageManager->getPageClass());
        }

        public function testGetBaseRepository()
        {
            $this->assertEquals($this->repos, $this->pageManager->getBaseRepository());
        }

        public function testPageTypeDecoration()
        {
            $types = [
                'zicht-test-some-bundle-some' => \ZichtTest\Bundle\SomeBundle\Entity\Page\SomePage::class,
                'zicht-test-some-bundle-some-other' => \ZichtTest\Bundle\SomeBundle\Entity\Page\SomeOtherPage::class,
            ];
            $this->pageManager->setPageTypes(array_values($types));

            $c = new \Doctrine\ORM\Mapping\ClassMetadata($this->pageClassName);
            $this->pageManager->decorateClassMetaData($c);

            $discriminatorMap = $types;
            $discriminatorMap['page'] = $this->pageClassName;

            $this->assertEquals($discriminatorMap, $c->discriminatorMap);
            $this->assertEquals(array_values($types), array_values($c->subClasses));
        }

        public function testContentItemTypeDecoration()
        {
            $types = [
                'zicht-test-some-bundle-some' => \ZichtTest\Bundle\SomeBundle\Entity\ContentItem\SomeContentItem::class,
                'zicht-test-some-bundle-some-other' => \ZichtTest\Bundle\SomeBundle\Entity\ContentItem\SomeOtherContentItem::class,
            ];
            $this->pageManager->setContentItemTypes(array_values($types));

            $c = new \Doctrine\ORM\Mapping\ClassMetadata($this->contentItemClassName);
            $this->pageManager->decorateClassMetaData($c);

            $discriminatorMap = $types;
            $discriminatorMap['contentitem'] = $this->contentItemClassName;

            $this->assertEquals($discriminatorMap, $c->discriminatorMap);
            $this->assertEquals(array_values($types), array_values($c->subClasses));
        }

        public function testFindForViewThrowsNotFoundHttpExceptionIfNotFoundInTable()
        {
            $this->expectException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
            $connection = $this->stubConnection();
            $statement = $this->createMock(class_exists('\Doctrine\DBAL\Result') ? 'Doctrine\DBAL\Result' : 'Doctrine\DBAL\Driver\Result');
            $statement->method('fetchOne')->willReturn(null);
            $connection->expects($this->once())->method('executeQuery')->will($this->returnValue($statement));
            $this->pageManager->findForView('foo');
        }

        public function testFindForViewThrowsNotFoundHttpExceptionIfNotFoundInRepository()
        {
            $this->expectException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
            $this->stubPage(null);
        }

        public function testFindForViewSetsLoadedPageIfLoadIsSuccessful()
        {
            $this->eventDispatcher->expects($this->once())->method('dispatch');
            $p = new \My\PageBundle\Entity\FooBarPage();
            $this->assertEquals($p, $this->stubPage($p));
            $this->assertEquals($p, $this->pageManager->getLoadedPage());
        }

        public function testGetLoadedPageWillCallCallableIfNoLoadedPage()
        {
            $called = false;
            $fn = function () use (&$called) {
                $called = true;
                return new \My\PageBundle\Entity\FooBarPage();
            };
            $this->pageManager->getLoadedPage($fn);
            $this->assertTrue($called);
        }

        public function testGetLoadedPageWillNotCallCallableIfPageAlreadyLoaded()
        {
            $called = false;
            $this->pageManager->setLoadedPage(new \My\PageBundle\Entity\FooBarPage());
            $fn = function () use (&$called) {
                $called = true;
                return new \My\PageBundle\Entity\FooBarPage();
            };
            $this->pageManager->getLoadedPage($fn);
            $this->assertFalse($called);
        }

        public function testGetLoadedPageWillThrowNotFoundHttpExceptionIfLoaderReturnsNothing()
        {
            $this->expectException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
            $called = false;
            $fn = function () use (&$called) {
                $called = true;
                return null;
            };
            $this->pageManager->getLoadedPage($fn);
        }

        public function testGetLoadedPageWillThrowNotFoundHttpExceptionIfLoaderIsNotPassed()
        {
            $this->expectException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
            $this->pageManager->getLoadedPage();
        }

        public function testFindAllProxiesToBaseRepository()
        {
            $this->repos->expects($this->once())->method('findAll');
            $this->pageManager->findAll();
        }

        public function testFindByPage()
        {
            $this->doctrine->expects($this->once())->method('getRepository')->with('Qux\Foo')->will($this->returnValue($this->repos));
            $ret = [
                'a', 'b',
            ];
            $conditions = ['foo' => 'bar'];
            $this->repos->expects($this->once())->method('findOneBy')->with($conditions)->will($this->returnValue($ret));
            $this->assertEquals($ret, $this->pageManager->findPageBy('Qux\Foo', $conditions));
        }

        public function testFindByWillThrowNotFoundExceptionIfNotFound()
        {
            $this->expectException('\Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
            $this->doctrine->expects($this->once())->method('getRepository')->with('Qux\Foo')->will($this->returnValue($this->repos));
            $ret = null;
            $conditions = ['foo' => 'bar'];
            $this->repos->expects($this->once())->method('findOneBy')->with($conditions)->will($this->returnValue($ret));
            $this->pageManager->findPageBy('Qux\Foo', $conditions);
        }

        protected function stubPage($value)
        {
            $connection = $this->stubConnection();
            $statement = $this->createMock(class_exists('\Doctrine\DBAL\Result') ? 'Doctrine\DBAL\Result' : 'Doctrine\DBAL\Driver\Result');
            $statement->method('fetchOne')->willReturn('bar');
            $connection->expects($this->once())->method('executeQuery')->will($this->returnValue($statement));
            $this->managerForClass->expects($this->once())->method('getClassMetaData')->with($this->pageClassName)->will(
                $this->returnValue(
                    (object)[
                        'discriminatorMap' => [
                            'bar' => 'Acme\Bar',
                        ],
                    ]
                )
            );
            $this->repos->expects($this->once())->method('find')->will($this->returnValue($value));
            return $this->pageManager->findForView('foo');
        }

        protected function stubConnection()
        {
            $connection = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
            $this->doctrine->expects($this->any())->method('getConnection')->will($this->returnValue($connection));
            return $connection;
        }
    }
}
