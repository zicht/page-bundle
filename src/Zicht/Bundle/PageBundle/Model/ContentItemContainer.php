<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
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
}