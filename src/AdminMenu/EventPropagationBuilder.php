<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\AdminMenu;

use Sonata\AdminBundle\Admin\Pool;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
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
    /** @var Pool */
    protected $sonata;

    /** @var Provider */
    protected $pageUrlProvider;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * Construct with the specified admin pool
     */
    public function __construct(Pool $sonata = null, Provider $pageUrlProvider = null, EventDispatcherInterface $eventDispatcher)
    {
        $this->sonata = $sonata;
        $this->pageUrlProvider = $pageUrlProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function buildAndForwardEvent(Event $e): void
    {
        if (!$e instanceof PageViewEvent) {
            return;
        }

        $page = $e->getPage();
        $admin = $this->sonata->getAdminByClass(get_class($page));

        if ($admin === null) {
            $admin = $this->sonata->getAdminByClass(get_parent_class($page));
        }

        if ($page->getId() && $admin !== null) {
            $title = $e->getPage()->getTitle();
            $this->eventDispatcher->dispatch(
                new MenuEvent(
                    $admin->generateObjectUrl('edit', $e->getPage()),
                    sprintf(
                        'Beheer pagina "%s"',
                        strlen($title) > 20 ? substr($title, 0, 20) . '...' : $title
                    )
                ),
                AdminEvents::MENU_EVENT
            );
        }
    }
}
