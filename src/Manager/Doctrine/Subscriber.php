<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Manager\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Zicht\Bundle\PageBundle\Manager\PageManager;

/**
 * Subscriber for loading the class metadata for content items and pages. Delegates to
 * PageManager::decorateClassMetaData()
 */
class Subscriber implements EventSubscriber
{
    /** @var PageManager */
    private $pageManager;

    public function __construct(PageManager $pageManager)
    {
        $this->pageManager = $pageManager;
    }

    public function getManager(): PageManager
    {
        return $this->pageManager;
    }

    /**
     * Delegates to PageManager::decorateClassMetaData to load the class meta data
     */
    public function loadClassMetaData(LoadClassMetadataEventArgs $args): void
    {
        $this->getManager()->decorateClassMetaData($args->getClassMetadata());
    }

    public function getSubscribedEvents()
    {
        return [
            Events::loadClassMetadata,
        ];
    }
}
