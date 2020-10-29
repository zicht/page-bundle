<?php declare(strict_types=1);
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Event;

use Zicht\Bundle\AdminBundle\Event\ObjectDuplicateEvent;
use Zicht\Bundle\PageBundle\Entity\Page;

/**
 * This listener listens to the event 'zicht_admin.object_duplicate' dispatched in the CRUDController in the admin-bundle
 */
class AdminObjectDuplicateListener
{
    public function onZichtadminObjectduplicate(ObjectDuplicateEvent $event): void
    {
        if (!($event->getOldObject() instanceof Page)){
            return;
        }
        $newPage = $event->getNewObject();
        $oldPage = $event->getOldObject();
        
        if (method_exists($newPage, 'setIsPublic')){
            $newPage->setIsPublic(false);
        }
    }
}
