<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\AdminMenu;

use Sonata\AdminBundle\Admin\Pool;
use Symfony\Component\EventDispatcher\Event;
use Zicht\Bundle\AdminBundle\Event\AdminEvents;
use Zicht\Bundle\AdminBundle\Event\MenuEvent;
use Zicht\Bundle\AdminBundle\Event\PropagationInterface;
use Zicht\Bundle\PageBundle\Event\PageViewEvent;
use Zicht\Bundle\UrlBundle\Url\Provider;

/**
 * Add links to edit a page to the zicht admin menu
 */
class EventPropagationBuilder implements PropagationInterface
{
    /**
     * @var Pool
     */
    protected $sonata;

    /**
     * @var Provider
     */
    protected $pageUrlProvider;

    /**
     * Construct with the specified admin pool
     *
     * @param Pool $sonata
     * @param Provider $pageUrlProvider
     */
    public function __construct(Pool $sonata = null, Provider $pageUrlProvider = null)
    {
        $this->sonata = $sonata;
        $this->pageUrlProvider = $pageUrlProvider;
    }

    /**
     * Build the relevant event and forward it.
     *
     * @param \Symfony\Component\EventDispatcher\Event $event
     * @return null|void
     */
    public function buildAndForwardEvent(Event $event)
    {
        if (!$event instanceof PageViewEvent) {
            return;
        }

        $page = $event->getPage();
        $admin = $this->sonata->getAdminByClass(get_class($page));

        if ($admin === null) {
            $admin = $this->sonata->getAdminByClass(get_parent_class($page));
        }

        if ($page->getId() && $admin !== null) {
            $title = $event->getPage()->getTitle();
            /** @var \Zicht\Bundle\PageBundle\Event\PageViewEvent $event */
            $event->getDispatcher()->dispatch(
                AdminEvents::MENU_EVENT,
                new MenuEvent(
                    $admin->generateObjectUrl('edit', $event->getPage()),
                    sprintf(
                        'Beheer pagina "%s"',
                        strlen($title) > 20 ? substr($title, 0, 20) . '...' : $title
                    )
                )
            );
        }
    }
}
