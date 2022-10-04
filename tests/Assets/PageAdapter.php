<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace ZichtTest\Bundle\PageBundle\Assets;

use Zicht\Bundle\PageBundle\Entity\Page;
use Zicht\Bundle\PageBundle\Model\ContentItemInterface;

class PageAdapter extends Page
{
    public function getContentItems($region = null)
    {
    }

    public function addContentItem(ContentItemInterface $contentItem)
    {
    }

    public function removeContentItem(ContentItemInterface $contentItem)
    {
    }

    public function getTitle()
    {
    }

    public function getId()
    {
    }
}
