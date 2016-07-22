<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Zicht\Bundle\PageBundle\Model\PageInterface;

/**
 * Event triggered whenever a page is loaded "for view" (PageManager::loadForView
 */
class PageViewEvent extends Event
{
    /**
     * Constructs the event for the specified page.
     *
     * @param \Zicht\Bundle\PageBundle\Model\PageInterface $page
     */
    public function __construct(PageInterface $page)
    {
        $this->page = $page;
    }


    /**
     * Return the page attached to this event
     *
     * @return \Zicht\Bundle\PageBundle\Model\PageInterface
     */
    public function getPage()
    {
        return $this->page;
    }
}