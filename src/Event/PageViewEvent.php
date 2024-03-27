<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Zicht\Bundle\PageBundle\Model\PageInterface;

/**
 * Event triggered whenever a page is loaded "for view" (PageManager::loadForView
 */
class PageViewEvent extends Event
{
    /** @var PageInterface */
    private $page;

    /**
     * Constructs the event for the specified page.
     */
    public function __construct(PageInterface $page)
    {
        $this->page = $page;
    }

    /**
     * Return the page attached to this event
     *
     * @return PageInterface
     */
    public function getPage()
    {
        return $this->page;
    }
}
