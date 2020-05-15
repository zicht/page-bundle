<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Manager\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Zicht\Bundle\PageBundle\Manager\PageManager;

/**
 * Subscriber for loading the class metadata for content items and pages. Delegates to
 * PageManager::decorateClassMetaData()
 */
class Subscriber implements EventSubscriber
{
    /**
     * @var PageManager
     */
    private $pageManager;

    public function __construct(PageManager $pageManager)
    {
        $this->pageManager = $pageManager;
    }

    /**
     * Returns the page manager service.
     *
     * @return PageManager
     */
    public function getManager()
    {
        return $this->pageManager;
    }

    /**
     * Delegates to PageManager::decorateClassMetaData to load the class meta data
     *
     * @param \Doctrine\ORM\Event\LoadClassMetadataEventArgs $args
     * @return void
     */
    public function loadClassMetaData(LoadClassMetadataEventArgs $args)
    {
        $this->getManager()->decorateClassMetaData($args->getClassMetadata());
    }

    /**
     * @{inheritDoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            Events::loadClassMetadata
        );
    }
}
