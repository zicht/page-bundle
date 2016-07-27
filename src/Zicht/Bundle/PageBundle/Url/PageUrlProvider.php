<?php
/**
 * @author Gerard van Helden <gerard@zicht.nl>
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Url;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Zicht\Bundle\PageBundle\Model\PageInterface;
use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\UrlBundle\Url\ListableProvider;
use Zicht\Bundle\UrlBundle\Url\SuggestableProvider;
use Zicht\Bundle\UrlBundle\Url\AbstractRoutingProvider;

/**
 * Provides urls for page objects.
 */
class PageUrlProvider extends AbstractRoutingProvider implements SuggestableProvider, ListableProvider
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
    public function routing($page, array $options = array())
    {
        if (is_callable(array($page, 'getLanguage')) && $page->getLanguage()) {
            return array(
                'zicht_page_page_view',
                array(
                    'id' => $page->getId(),
                    '_locale' => $page->getLanguage()
                )
            );
        } else {
            return array(
                'zicht_page_page_view',
                array(
                    'id' => $page->getId()
                )
            );
        }
    }

    /**
     * @{inheritDoc}
     */
    public function suggest($pattern)
    {
        $pages = $this->pageManager->getBaseRepository()->createQueryBuilder('p')
            ->andWhere('p.title LIKE :pattern')
            ->setMaxResults(30)
            ->getQuery()
            ->execute(array('pattern' => '%' . $pattern . '%'));

        $suggestions = array();
        foreach ($pages as $page) {
            $suggestions[]= array(
                'value' => $this->url($page),
                'label' => $this->getLabel($page)
            );
        }

        return $suggestions;
    }

    /**
     * @{inheritDoc}
     */
    public function all(SecurityContextInterface $security)
    {
        $ret = array();
        $pages = $this->pageManager->getBaseRepository()->createQueryBuilder('p')
            ->orderBy('p.title')
            ->getQuery()
            ->execute();

        foreach ($pages as $page) {
            if ($security->isGranted(array('VIEW'), $page)) {
                $ret[] = array(
                    'value' => $this->url($page),
                    'title' => $this->getLabel($page)
                );
            }
        }
        return $ret;
    }

    /**
     * Returns the label of the page to use in url suggestions
     *
     * @param \Zicht\Bundle\PageBundle\Model\PageInterface $page
     * @return string
     */
    public function getLabel(PageInterface $page)
    {
        return sprintf(
            '%s (pagina, %s)',
            $page->getTitle(),
            $page->getDisplayType()
        );
    }
}
