<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Event;

/**
 * Page events
 */
final class PageEvents
{
    /**
     * Triggered whenever the PageManager loads a Page for view.
     */
    const PAGE_VIEW = 'zicht_page.view';
}
