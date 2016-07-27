<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Common utility base controller
 */
abstract class AbstractController extends Controller
{
    /**
     * Utility method that returns the page manager.
     *
     * @return \Zicht\Bundle\PageBundle\Manager\PageManager
     */
    public function getPageManager()
    {
        return $this->get('zicht_page.page_manager');
    }
}
