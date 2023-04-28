<?php
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;
use Zicht\Bundle\PageBundle\Manager\PageManager;

/**
 * @deprecated extend {@see \Symfony\Bundle\FrameworkBundle\Controller\AbstractController} directly.
 */
abstract class AbstractController extends BaseAbstractController
{
    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), ['zicht_page.page_manager' => PageManager::class]);
    }

    /** @deprecated Use constructor injection in your controller to inject the PageManager instead. */
    public function getPageManager(): PageManager
    {
        trigger_deprecation('zicht/page-bundle', '8.2', 'Method "%s()" is deprecated, use constructor injection in your controller to inject the "PageManager" instead.', __METHOD__);
        return $this->get('zicht_page.page_manager');
    }
}
