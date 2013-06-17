<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Entity;

use \Zicht\Util\Str;
use \Zicht\Bundle\PageBundle\Model\PageInterface;

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
}