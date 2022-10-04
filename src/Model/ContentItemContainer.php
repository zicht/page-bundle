<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Model;

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
     * @param null $region
     * @return mixed
     */
    public function getContentItems($region = null);

    /**
     * @return mixed
     */
    public function addContentItem(ContentItemInterface $contentItem);

    /**
     * @return mixed
     */
    public function removeContentItem(ContentItemInterface $contentItem);
}
