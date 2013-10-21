<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Entity;

use \Zicht\Util\Str;
use \Zicht\Bundle\PageBundle\Model\PageInterface;
use \Zicht\Bundle\PageBundle\Model\ContentItemMatrix;

/**
 * Base class for pages.
 */
abstract class Page implements PageInterface
{
    /**
     * Constructor stub
     */
    public function __construct()
    {
    }


    /**
     * @{inheritDoc}
     */
    public function getTemplateName()
    {
        return Str::infix(Str::rstrip(Str::classname(get_class($this)), 'Page'), '-');
    }


    public function getLanguage()
    {
        return null;
    }


    /**
     * Returns the content item matrix for the item.
     *
     * @return ContentItemMatrix
     */
    public function getContentItemMatrix()
    {
    }

    /**
     * A page must be able to display it's own type as a human readable string.
     *
     * @return string
     */
    public function getDisplayType()
    {
        return get_class($this);
    }
}