<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Entity;

/**
 * Base class for ContentItem entities.
 */
interface ContentItemTemplatableInterface
{
    /**
     * @return string
     */
    public function getTemplateName();
}
