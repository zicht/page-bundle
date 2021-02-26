<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Entity;

use Zicht\Bundle\PageBundle\Model\ContentItemMatrix;
use Zicht\Bundle\PageBundle\Model\PageInterface;
use Zicht\Util\Str;

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

    /**
     * May be implemented to support translated pages.
     *
     * @return string|null
     */
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

    /**
     * By default, pages are never public (sane default). If you need your own logic,
     * implement it here, the PageVoter will respect this.
     *
     * @return bool
     */
    public function isPublic()
    {
        return false;
    }
}
