<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\AdminMenu;

use Symfony\Component\Routing\Router;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zicht\Bundle\AdminBundle\Event\AdminEvents;
use Zicht\Bundle\AdminBundle\Event\MenuEvent;
use Zicht\Bundle\AdminBundle\Event\PropagationInterface;
use Zicht\Bundle\PageBundle\Entity\Page;
use Zicht\Bundle\PageBundle\Event\PageViewEvent;

/**
 * Add links to see zz translations to the zicht admin menu
 */
class TranslatePageEventPropagationBuilder implements PropagationInterface
{
    /** @var Router */
    private $router;

    /** @var EventDispatcher */
    private $eventDispatcher;

    public function __construct(Router $router, EventDispatcherInterface $eventDispatcher)
    {
        $this->router = $router;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Build the relevant event and forward it.
     *
     * @return mixed|void
     */
    public function buildAndForwardEvent(Event $event)
    {
        if (!$event instanceof PageViewEvent) {
            return;
        }

        if (!$this->router) {
            return;
        }

        /** @var Page $page */
        $page = $event->getPage();
        if (!$page->getId()) {
            return;
        }

        $this->eventDispatcher->dispatch(
            new MenuEvent(
                $this->router->generate(
                    'zicht_page_page_view',
                    [
                    'id' => $page->getId(),
                    '_locale' => 'zz',
                    ]
                ),
                'Vertalingen'
            ),
            AdminEvents::MENU_EVENT
        );

        $this->eventDispatcher->dispatch(
            new MenuEvent(
                $this->router->generate(
                    'zicht_page_page_view',
                    [
                    'id' => $page->getId(),
                    '_locale' => $page->getLanguage(),
                    ]
                ),
                'Pagina herladen'
            ),
            AdminEvents::MENU_EVENT
        );
    }
}
