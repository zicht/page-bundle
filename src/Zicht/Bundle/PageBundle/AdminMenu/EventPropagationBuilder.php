<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */
namespace Zicht\Bundle\PageBundle\AdminMenu;

use Sro\Service\ContextManager\ProviderInterface;
use Symfony\Component\EventDispatcher\Event;
use Sonata\AdminBundle\Admin\Pool;
use Zicht\Bundle\AdminBundle\Event\AdminEvents;
use Zicht\Bundle\AdminBundle\Event\MenuEvent;
use Zicht\Bundle\AdminBundle\Event\PropagationInterface;
use Zicht\Bundle\PageBundle\Event\PageViewEvent;

/**
 * Propagates a PageView event as an AdminMenu event.
 */
class EventPropagationBuilder implements PropagationInterface
{
    /**
     * Construct with the specified admin pool
     *
     * @param \Sonata\AdminBundle\Admin\Pool $sonata
     */
    public function __construct(Pool $sonata, ProviderInterface $pageUrlProvider)
    {
        $this->sonata = $sonata;
        $this->pageUrlProvider = $pageUrlProvider;
    }


    /**
     * Build the relevant event and forward it.
     *
     * @param \Symfony\Component\EventDispatcher\Event $e
     * @return mixed|void
     */
    public function buildAndForwardEvent(Event $e)
    {
        if (!$e instanceof PageViewEvent) {
            return;
        }

        $page = $e->getPage();
        if (
            ($admin = $this->sonata->getAdminByClass(get_class($page)))
            || ($admin = $this->sonata->getAdminByClass(get_parent_class($page)))
        ) {
            $title = $e->getPage()->getTitle();
            /** @var \Zicht\Bundle\PageBundle\Event\PageViewEvent $e */
            $e->getDispatcher()->dispatch(
                AdminEvents::MENU_EVENT,
                new MenuEvent(
                    $admin->generateObjectUrl('edit', $e->getPage()),
                    sprintf(
                        'Beheer pagina "%s"',
                        strlen($title) > 20 ? substr($title, 0, 20) . '...' : $title
                    )
                )
            );

            $zzPage = clone $e->getPage();
            $zzPage->setLanguage('zz');

            $e->getDispatcher()->dispatch(
                AdminEvents::MENU_EVENT,
                new MenuEvent(
                    $this->pageUrlProvider->url($zzPage),
                    'Vertalingen'
                )
            );

            $e->getDispatcher()->dispatch(
                AdminEvents::MENU_EVENT,
                new MenuEvent(
                    $this->pageUrlProvider->url($e->getPage()),
                    'Pagina herladen'
                )
            );
        }
    }
}