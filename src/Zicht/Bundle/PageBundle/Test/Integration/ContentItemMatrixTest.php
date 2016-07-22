<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Test\Integration;

use Zicht\Bundle\PageBundle\Controller\PageController;
use Zicht\Bundle\PageBundle\Model\ContentItemMatrix;

class ContentItemMatrixTest extends AbstractPageTest
{
    /**
     * @dataProvider contentItemMatrix
     */
    public function testRenderWillRetrieveCorrectRegions($pageTypeName, $pageClassName, $region = null, $contentItemClassName = null)
    {
        $this->markTestSkipped();


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
        $pageController = $this->createPageController();
        $pageController->renderPage($mockPage)->getContent();

        $called = array();
        foreach ($recorded as $calls) {
            $called[]= $calls[0];
        }
        $this->assertContains($region, $called);
    }


    public function contentItemMatrix()
    {
        $pageManager = $this->createPageManager();
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
