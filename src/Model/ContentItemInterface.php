<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Model;

/**
 * All items should identify their own type.
 */
interface ContentItemInterface
{
    /**
     * Return the type name for the content item, usually it's class name.
     *
     * @return string
     */
    public function getType();

    /**
     * @return string
     */
    public function getInternalName();
}
