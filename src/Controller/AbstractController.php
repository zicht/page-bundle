<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Zicht\Bundle\PageBundle\Manager\PageManager;

/**
 * Common utility base controller
 */
abstract class AbstractController extends BaseAbstractController
{
    public static function getSubscribedServices()
    {
        return array_merge(parent::getSubscribedServices(), ['zicht_page.page_manager' => PageManager::class]);
    }

    /**
     * Utility method that returns the page manager.
     *
     * @return PageManager
     */
    public function getPageManager()
    {
        return $this->get('zicht_page.page_manager');
    }
}
