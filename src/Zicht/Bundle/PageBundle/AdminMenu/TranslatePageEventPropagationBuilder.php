<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\AdminMenu;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Routing\Router;
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

    /**
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Build the relevant event and forward it.
     *
     * @param \Symfony\Component\EventDispatcher\Event $event
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

        $event->getDispatcher()->dispatch(
            AdminEvents::MENU_EVENT,
            new MenuEvent(
                $this->router->generate('zicht_page_page_view', [
                    'id' => $page->getId(),
                    '_locale' => 'zz',
                ]),
                'Vertalingen'
            )
        );

        $event->getDispatcher()->dispatch(
            AdminEvents::MENU_EVENT,
            new MenuEvent(
                $this->router->generate('zicht_page_page_view', [
                    'id' => $page->getId(),
                    '_locale' => $page->getLanguage(),
                ]),
                'Pagina herladen'
            )
        );
    }
}
