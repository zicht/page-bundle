<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Test\Integration;

use Zicht\Bundle\PageBundle\Model\ContentItemMatrix;

class ContentItemMatrixTest extends AbstractPageTest
{
    /**
     * Testing whether the regions are correct
     *
     * @param string $pageTypeName
     * @param string $pageClassName
     * @param null $region
     * @param null $contentItemClassName
     * @dataProvider contentItemMatrix
     */
    public function testRenderWillRetrieveCorrectRegions($pageTypeName, $pageClassName, $region = null, $contentItemClassName = null)
    {
        $this->markTestSkipped();

        if (null === $contentItemClassName || null === $region) {
            $this->markTestSkipped("{$pageClassName} has no content item matrix");
        } elseif (!class_exists($contentItemClassName)) {
            $this->fail("{$contentItemClassName} does not exist");
        }

        /** @var \Zicht\Bundle\PageBundle\Model\PageInterface $p */
        $realPage = new $pageClassName();

        $recorded = [];

        $pageUnderTest = clone $realPage;

        $contentItem = new $contentItemClassName();
        $contentItem->setRegion($region);
        $pageUnderTest->addContentItem($contentItem);

        $mockPage = $this->getMock($pageClassName, ['getContentItems', 'getTemplateName']);
        $mockPage
            ->expects($this->atLeastOnce())
            ->method('getContentItems')
            ->will(
                $this->returnCallback(
                    function () use (&$recorded, $pageUnderTest) {
                        $args = func_get_args();
                        $recorded[] = $args;
                        $ret = call_user_func_array([$pageUnderTest, 'getContentItems'], $args);
                        return $ret;
                    }
                )
            );

        $mockPage->expects($this->any())
            ->method('getTemplateName')
            ->will(
                $this->returnCallback(
                    function () use ($pageUnderTest) {
                        return $pageUnderTest->getTemplateName();
                    }
                )
            );

        /** @var \Zicht\Bundle\PageBundle\Entity\ContentItem $item */
        $pageController = $this->createPageController();
        $pageController->renderPage($mockPage)->getContent();

        $called = [];
        foreach ($recorded as $calls) {
            $called[] = $calls[0];
        }
        $this->assertContains($region, $called);
    }

    /**
     * @return array
     */
    public function contentItemMatrix()
    {
        $pageManager = $this->createPageManager();
        $mappings = $pageManager->getMappings();

        $ret = [];
        foreach ($mappings[$pageManager->getPageClass()] as $name => $pageClassName) {
            $realPage = new $pageClassName();
            $matrix = $realPage->getContentItemMatrix();

            /** @var ContentItemMatrix $matrix */
            if (!$matrix) {
                $ret[] = [$name, $pageClassName];
                continue;
            }

            foreach ($matrix->getTypes() as $contentItemClassName) {
                foreach ($matrix->getRegions($contentItemClassName) as $region) {
                    $ret[] = [$name, $pageClassName, $region, $contentItemClassName];
                }
            }
        }

        return $ret;
    }
}
