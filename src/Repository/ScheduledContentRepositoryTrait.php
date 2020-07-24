<?php

namespace Zicht\Bundle\PageBundle\Repository;

use Doctrine\Common\Collections\Criteria;

trait ScheduledContentRepositoryTrait
{
    /**
     * Usage: $queryBuilder->addCriteria()
     */
    private function generatePublisedWhereClausesCriteria(string $alias = 'p', \DateTimeInterface $refDate = null): Criteria
    {
        if (null === $refDate) {
            $refDate = new \DateTimeImmutable('now');
        }
        $expr = Criteria::expr();

        return Criteria::create()
            ->andWhere(
                $expr->eq($alias . '.isPublic', true)
            )->andWhere(
                $expr->orX(
                    $expr->andX(
                        $expr->isNull($alias . '.dateScheduledFrom'),
                        $expr->isNull($alias . '.dateScheduledTill'),
                    ),
                    $expr->andX(
                        $expr->lte($alias . '.dateScheduledFrom', $refDate),
                        $expr->isNull($alias . '.dateScheduledTill')
                    ),
                    $expr->andX(
                        $expr->isNull($alias . '.dateScheduledFrom'),
                        $expr->gte($alias . '.dateScheduledTill', $refDate)
                    ),
                    $expr->andX(
                        $expr->lte($alias . '.dateScheduledFrom', $refDate),
                        $expr->gte($alias . '.dateScheduledTill', $refDate)
                    )
                )
            );
    }
}
