<?php declare(strict_types=1);
/**
 * @copyright Zicht Online <https://zicht.nl>
 */

namespace Zicht\Bundle\PageBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zicht\Bundle\PageBundle\Manager\PageManager;
use Zicht\Bundle\PageBundle\Model\ContentItemInterface;
use Zicht\Bundle\PageBundle\Model\PageInterface;

final class DebugPagesController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    private PageManager $pageManager;

    public function __construct(EntityManagerInterface $entityManager, PageManager $pageManager)
    {
        $this->entityManager = $entityManager;
        $this->pageManager = $pageManager;
    }

    public function showProjectPageTypeLinksAndInfo(Request $request): Response
    {
        /** @var EntityRepository $repository */
        $repository = $this->pageManager->getBaseRepository();

        $pagesInfo = [];
        $totalCount = 0;
        $locale = $request->getLocale();
        foreach ($this->pageManager->getPageTypes() as $pageType) {
            $qb = $repository->createQueryBuilder('p');
            $qb->where($qb->expr()->isInstanceOf('p', $pageType));
            if ($locale && $this->pageSupportsLanguage($pageType)) {
                $qb->andWhere($qb->expr()->eq('p.language', ':language'));
                $qb->setParameter('language', $locale);
            }
            $qb->orderBy('RAND()');

            $count = (clone $qb)->select('COUNT(p.id)')->getQuery()->getSingleScalarResult();
            /** @var PageInterface $page */
            $page = $qb->setMaxResults(1)->getQuery()->getOneOrNullResult();
            $contentItemTypes = array_count_values(
                array_map(
                    function (ContentItemInterface $contentItem) {
                        return $contentItem->getType();
                    },
                    $page ? ($page->getContentItems() instanceof \Traversable ? iterator_to_array($page->getContentItems()) : $page->getContentItems()) : []
                )
            );
            $pagesInfo[$pageType] = [
                'page' => $page,
                'count' => $count,
                'content_items' => $contentItemTypes,
            ];
            $totalCount += $count;
        }

        return $this->render(
            '@ZichtPage/debug_pages/project_page_type_links_and_info.html.twig',
            [
                'pages_info' => $pagesInfo,
                'total_count' => $totalCount,
            ]
        );
    }

    private function pageSupportsLanguage(string $pageType): bool
    {
        return $this->entityManager->getClassMetadata($pageType)->hasField('language');
    }
}
