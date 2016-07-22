<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Manager\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Subscriber for loading the class metadata for content items and pages. Delegates to
 * PageManager::decorateClassMetaData()
 */
class Subscriber implements EventSubscriber
{
    /**
     * Construct the subscriber.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }


    /**
     * Returns the page manager service.
     *
     * @return \Zicht\Bundle\PageBundle\Manager\PageManager
     */
    public function getManager()
    {
        return $this->container->get('zicht_page.page_manager');
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