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
    class FooBarPage extends \Zicht\Bundle\PageBundle\Entity\Page {
        public function getTitle()
        {
            return '';
        }
    }
}

namespace ZichtTest\Bundle\PageBundle\AdminMenu {

    class PageManagerTest extends \PHPUnit_Framework_TestCase
    {
        protected $doctrine, $em, $eventDispatcher, $pageClassName, $contentItemClassName, $pageManager;

        function setUp()
        {
            $this->doctrine = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')->disableOriginalConstructor()->getMock();
            $this->em = $this->getMockBuilder('Doctrine\ORM\EntityRepository')->disableOriginalConstructor()->getMock();
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
            $rand = rand(1, 100);
            $this->doctrine->expects($this->once())->method('getRepository')->with($this->pageClassName)->will($this->returnValue($rand));
            $this->assertEquals($rand, $this->pageManager->getBaseRepository());
        }


        function testPageTypeDecoration()
        {
            $types = array(
                'some' => 'SomePage',
                'some-other' => 'SomeOtherPage'
            );
            $this->pageManager->setPageTypes(array_values($types));

            $c = new \Doctrine\ORM\Mapping\ClassMetadata($this->pageClassName);
            $this->pageManager->decorateClassMetaData($c);

            $this->assertEquals($types, $c->discriminatorMap);
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

            $this->assertEquals($types, $c->discriminatorMap);
            $this->assertEquals(array_values($types), array_values($c->subClasses));
        }
    }
}
