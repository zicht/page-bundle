<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Model;

use Zicht\Bundle\PageBundle\Entity\ContentItem;

/**
 * If an item that contains ContentItems implements this interface, the ContentItemTypeType and ContentItemRegionType
 * know how to render the available options for it.
 */
interface ContentItemContainer
{
    /**
     * Returns the content item matrix for the item.
     *
     * @return ContentItemMatrix
     */
    public function getContentItemMatrix();

    /**
     * Returns a list of ContentItems
     *
     * @param string|null $region
     * @return (\Traversable<int, ContentItem>&\Countable)|list<ContentItem>
     */
    public function getContentItems($region = null);

    public function addContentItem(ContentItemInterface $contentItem);

    public function removeContentItem(ContentItemInterface $contentItem);
}
