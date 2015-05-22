<?php

namespace Zicht\Bundle\PageBundle\Test\Integration;

use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use \Zicht\Bundle\PageBundle\Controller\PageController;
use \Zicht\Bundle\PageBundle\Model\ContentItemMatrix;

/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */


class ContentItemMatrixTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \AppKernel
     */
    public $kernel;

    public function setUp()
    {
        $this->kernel = new \AppKernel();
        $this->kernel->boot();

        // we need to synthesize the request, because it could be used by the controller or it's templates.
        $request = new Request();

        $this->kernel->getContainer()->get('event_dispatcher')->dispatch(
            KernelEvents::REQUEST,
            new GetResponseEvent($this->kernel, $request, HttpKernelInterface::MASTER_REQUEST)
        );
        $this->kernel->getContainer()->enterScope('request');
        $this->kernel->getContainer()->set('request', $request, 'request');
    }


    /**
     * @dataProvider contentItemMatrix
     */
    public function testMatrix($pageTypeName, $pageClassName, $region = null, $contentItemClassName = null)
    {
        if (null === $contentItemClassName || null === $region) {
            $this->markTestSkipped("{$pageClassName} has no content item matrix");
        } else if (!class_exists($contentItemClassName)) {
            $this->fail("{$contentItemClassName} does not exist");
        }

        /** @var \Zicht\Bundle\PageBundle\Model\PageInterface $p */
        $realPage = new $pageClassName;

        $recorded = array();

        $pageUnderTest = clone $realPage;

        $contentItem = new $contentItemClassName;
        $contentItem->setRegion($region);
        $pageUnderTest->addContentItem($contentItem);

        $mockPage = $this->getMock($pageClassName, array('getContentItems', 'getTemplateName'));
        $mockPage
            ->expects($this->atLeastOnce())
            ->method('getContentItems')
            ->will($this->returnCallback(function() use(&$recorded, $pageUnderTest, $region) {
                $args = func_get_args();
                $recorded[]= $args;
                $ret = call_user_func_array(array($pageUnderTest, 'getContentItems'), $args);
                return $ret;
            }));

        $mockPage->expects($this->any())->method('getTemplateName')->will($this->returnCallback(function() use($pageUnderTest) {
            return $pageUnderTest->getTemplateName();
        }));

        /** @var \Zicht\Bundle\PageBundle\Entity\ContentItem $item */
        $pageController = new PageController();
        $pageController->setContainer($this->kernel->getContainer());
        $pageController->renderPage($mockPage)->getContent();

        $called = array();
        foreach ($recorded as $calls) {
            $called[]= $calls[0];
        }
        $this->assertContains($region, $called);
    }


    public function contentItemMatrix()
    {
        $kernel = new \AppKernel();
        $kernel->boot();

        $pageManager = $kernel->getContainer()->get('zicht_page.page_manager');

        $mappings = $pageManager->getMappings();

        $ret = array();
        foreach ($mappings[$pageManager->getPageClass()] as $name => $pageClassName) {
            $realPage = new $pageClassName;
            $matrix = $realPage->getContentItemMatrix();

            /** @var ContentItemMatrix $matrix */
            if (!$matrix) {
                $ret[]= array($name, $pageClassName);
                continue;
            }

            foreach ($matrix->getTypes() as $contentItemClassName) {
                foreach ($matrix->getRegions($contentItemClassName) as $region) {
                    $ret[]= array($name, $pageClassName, $region, $contentItemClassName);
                }
            }
        }

        return $ret;
    }
}