<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Url;

use \Symfony\Component\Routing\RouterInterface;
use \Zicht\Bundle\UrlBundle\Url\SuggestableProvider;
use \Zicht\Bundle\PageBundle\Manager\PageManager;
use \Zicht\Bundle\UrlBundle\Url\AbstractRoutingProvider;

/**
 * Provides urls for page objects.
 */
class PageUrlProvider extends AbstractRoutingProvider implements SuggestableProvider
{
    /**
     * Constructs the provider
     *
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Zicht\Bundle\PageBundle\Manager\PageManager $pageManager
     */
    public function __construct(RouterInterface $router, PageManager $pageManager)
    {
        parent::__construct($router);
        $this->pageManager = $pageManager;
    }


    /**
     * @{inheritDoc}
     */
    public function supports($object)
    {
        $pageClassName = $this->pageManager->getPageClass();
        return ($object instanceof $pageClassName);
    }


    /**
     * @{inheritDoc}
     */
    public function routing($page)
    {
        return array(
            'zicht_page_page_view',
            array(
                'id' => $page->getId()
            )
        );
    }

    /**
     * @{inheritDoc}
     */
    public function suggest($pattern)
    {
        $pages = $this->pageManager->getBaseRepository()->createQueryBuilder('p')
            ->andWhere('p.title LIKE :pattern')
            ->getQuery()
            ->setMaxResults(30)
            ->execute(array('pattern' => '%' . $pattern . '%'))
        ;

        $suggestions = array();
        foreach ($pages as $page) {
            $suggestions[]= array(
                'value' => $this->url($page),
                'label' => sprintf('%s (pagina)', $page)
            );
        }

        return $suggestions;
    }
}