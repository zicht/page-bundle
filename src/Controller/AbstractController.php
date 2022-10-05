<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Zicht\Bundle\PageBundle\Manager\PageManager;

abstract class AbstractController extends BaseAbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), ['zicht_page.page_manager' => PageManager::class]);
    }

    public function getPageManager(): PageManager
    {
        return $this->get('zicht_page.page_manager');
    }
}
