<?php
/**
 * @copyright Zicht Online <http://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Url;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\PageBundle\Model\PageInterface;
use Zicht\Bundle\UrlBundle\Url\AbstractRoutingProvider;
use Zicht\Bundle\UrlBundle\Url\ListableProvider;
use Zicht\Bundle\UrlBundle\Url\SuggestableProvider;

/**
 * Provides urls for page objects.
 */
class PageUrlProvider extends AbstractRoutingProvider implements SuggestableProvider, ListableProvider
{
    public function __construct(RouterInterface $router, PageManager $pageManager)
    {
        parent::__construct($router);
        $this->pageManager = $pageManager;
    }

    public function supports($object)
    {
        $pageClassName = $this->pageManager->getPageClass();
        return ($object instanceof $pageClassName) && $object->getId();
    }

    public function routing($page, array $options = [])
    {
        if (is_callable([$page, 'getLanguage']) && $page->getLanguage()) {
            if (array_key_exists('_locale', $options)) {
                $locale = $options['_locale'];
            } else {
                $locale = $page->getLanguage();
            }

            return [
                'zicht_page_page_view',
                [
                    'id' => $page->getId(),
                    '_locale' => $locale,
                ],
            ];
        } else {
            return [
                'zicht_page_page_view',
                [
                    'id' => $page->getId(),
                ],
            ];
        }
    }

    public function suggest($pattern)
    {
        $pages = $this->pageManager->getBaseRepository()->createQueryBuilder('p')
            ->andWhere('p.title LIKE :pattern')
            ->setMaxResults(30)
            ->getQuery()
            ->execute(['pattern' => '%' . $pattern . '%']);

        $suggestions = [];
        foreach ($pages as $page) {
            $suggestions[] = [
                'value' => $this->url($page),
                'label' => $this->getLabel($page),
            ];
        }

        return $suggestions;
    }

    public function all(AuthorizationCheckerInterface $security)
    {
        $ret = [];
        $pages = $this->pageManager->getBaseRepository()->createQueryBuilder('p')
            ->orderBy('p.title')
            ->getQuery()
            ->execute();

        foreach ($pages as $page) {
            if ($security->isGranted(['VIEW'], $page)) {
                $ret[] = [
                    'value' => $this->url($page),
                    'title' => $this->getLabel($page),
                ];
            }
        }
        return $ret;
    }

    /**
     * Returns the label of the page to use in url suggestions
     *
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
