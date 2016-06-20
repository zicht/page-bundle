<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace {
    class SomePage {
    }
}

namespace My\PageBundle\Entity {

    use ZichtTest\Bundle\PageBundle\Assets\PageAdapter;

    class FooBarPage extends PageAdapter {
        public function getTitle()
        {
            return '';
        }

        /**
         * A page must always have an id
         *
         * @return mixed
         */
        public function getId()
        {
            // TODO: Implement getId() method.
        }

    }
}

namespace ZichtTest\Bundle\PageBundle\Manager {

    use Zicht\Util\Str;

    class PageManagerTest extends \PHPUnit_Framework_TestCase
    {
        protected $doctrine, $em, $repos, $eventDispatcher, $pageClassName, $contentItemClassName, $pageManager;

        function setUp()
        {
            $this->doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')->disableOriginalConstructor()->getMock();
            $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')->setMethods(
                array('getRepository', 'getClassMetaData'))->disableOriginalConstructor()->getMock();
            $this->repos = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->setMethods(array('find', 'findOneBy', 'findAll'))->disableOriginalConstructor()->getMock();
            $this->em->expects($this->any())->method('getRepository')->will($this->returnValue($this->repos));
            $this->doctrine->expects($this->any())->method('getManager')->will($this->returnValue($this->em));
            $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');
            $this->pageClassName = 'My\Page';
            $this->contentItemClassName = 'My\ContentItem';

            $this->pageManager = new \Zicht\Bundle\PageBundle\Manager\PageManager(
                $this->doctrine,
                $this->eventDispatcher,
                $this->pageClassName,
                $this->contentItemClassName
            );
        }


        function testGetTemplateWillReturnBundleTemplate() {
            $this->assertEquals(
                'MyPageBundle:Page:foo-bar.html.twig',
                $this->pageManager->getTemplate(new \My\PageBundle\Entity\FooBarPage())
            );
        }

        /**
         * @expectedException \RuntimeException
         */
        function testGetTemplateWillThrowExceptionIfBundleNameIsUndeterminable() {
            $this->pageManager->getTemplate(new \SomePage());
        }


        function testGetPageClassWillReturnPageClassName()
        {
            $this->assertEquals($this->pageClassName, $this->pageManager->getPageClass());
        }

        function testGetBaseRepository()
        {
            $this->assertEquals($this->repos, $this->pageManager->getBaseRepository());
        }


        function testPageTypeDecoration()
        {
            $types = array(
                'some' => 'SomePage',
                'some-other' => 'SomeOtherPage',
            );
            $this->pageManager->setPageTypes(array_values($types));

            $c = new \Doctrine\ORM\Mapping\ClassMetadata($this->pageClassName);
            $this->pageManager->decorateClassMetaData($c);

            $discriminatorMap = $types;
            $discriminatorMap['page'] = $this->pageClassName;

            $this->assertEquals($discriminatorMap, $c->discriminatorMap);
            $this->assertEquals(array_values($types), array_values($c->subClasses));
        }

        function testContentItemTypeDecoration()
        {
            $types = array(
                'some' => 'SomeContentItem',
                'some-other' => 'SomeOtherContentItem'
            );
            $this->pageManager->setContentItemTypes(array_values($types));

            $c = new \Doctrine\ORM\Mapping\ClassMetadata($this->contentItemClassName);
            $this->pageManager->decorateClassMetaData($c);

            $discriminatorMap = $types;
            $discriminatorMap['contentitem'] = $this->contentItemClassName;

            $this->assertEquals($discriminatorMap, $c->discriminatorMap);
            $this->assertEquals(array_values($types), array_values($c->subClasses));
        }


        /**
         * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
         */
        function testFindForViewThrowsNotFoundHttpExceptionIfNotFoundInTable()
        {
            $connection = $this->stubConnection();
            $connection->expects($this->once())->method('fetchColumn')->will($this->returnValue(null));
            $this->pageManager->findForView('foo');
        }


        /**
         * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
         */
        function testFindForViewThrowsNotFoundHttpExceptionIfNotFoundInRepository()
        {
            $this->stubPage(null);
        }


        function testFindForViewSetsLoadedPageIfLoadIsSuccessful()
        {
            $this->eventDispatcher->expects($this->once())->method('dispatch');
            $p = new \My\PageBundle\Entity\FooBarPage();
            $this->assertEquals($p, $this->stubPage($p));
            $this->assertEquals($p, $this->pageManager->getLoadedPage());
        }


        function testGetLoadedPageWillCallCallableIfNoLoadedPage()
        {
            $called = false;
            $fn = function() use(&$called) {
                $called = true;
                return new \My\PageBundle\Entity\FooBarPage();
            };
            $this->pageManager->getLoadedPage($fn);
            $this->assertTrue($called);
        }

        function testGetLoadedPageWillNotCallCallableIfPageAlreadyLoaded()
        {
            $called = false;
            $this->pageManager->setLoadedPage(new \My\PageBundle\Entity\FooBarPage());
            $fn = function() use(&$called) {
                $called = true;
                return new \My\PageBundle\Entity\FooBarPage();
            };
            $this->pageManager->getLoadedPage($fn);
            $this->assertFalse($called);
        }

        /**
         * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
         */
        function testGetLoadedPageWillThrowNotFoundHttpExceptionIfLoaderReturnsNothing()
        {
            $called = false;
            $fn = function() use(&$called) {
                $called = true;
                return null;
            };
            $this->pageManager->getLoadedPage($fn);
        }

        /**
         * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
         */
        function testGetLoadedPageWillThrowNotFoundHttpExceptionIfLoaderIsNotPassed()
        {
            $this->pageManager->getLoadedPage();
        }


        function testFindAllProxiesToBaseRepository()
        {
            $this->repos->expects($this->once())->method('findAll');
            $this->pageManager->findAll();
        }


        function testFindByPage()
        {
            $this->em->expects($this->once())->method('getRepository')->with('Qux\Foo')->will($this->returnValue($this->repos));
            $ret = array(
                'a', 'b'
            );
            $conditions = array('foo' => 'bar');
            $this->repos->expects($this->once())->method('findOneBy')->with($conditions)->will($this->returnValue($ret));
            $this->assertEquals($ret, $this->pageManager->findPageBy('Qux\Foo', $conditions));
        }

        /**
         * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
         */
        function testFindByWillThrowNotFoundExceptionIfNotFound()
        {
            $this->em->expects($this->once())->method('getRepository')->with('Qux\Foo')->will($this->returnValue($this->repos));
            $ret = null;
            $conditions = array('foo' => 'bar');
            $this->repos->expects($this->once())->method('findOneBy')->with($conditions)->will($this->returnValue($ret));
            $this->pageManager->findPageBy('Qux\Foo', $conditions);
        }


        protected function stubPage($value)
        {
            $connection = $this->stubConnection();
            $connection->expects($this->once())->method('fetchColumn')->will($this->returnValue('bar'));
            $this->em->expects($this->once())->method('getClassMetaData')->with($this->pageClassName)->will(
                $this->returnValue(
                    (object) array(
                        'discriminatorMap' => array(
                            'bar' => 'Acme\Bar'
                        )
                    )
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
