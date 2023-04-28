<?php

namespace Zicht\Bundle\PageBundle\Repository;

use Doctrine\Common\Collections\Criteria;

trait ScheduledContentRepositoryTrait
{
    /**
     * Usage: $queryBuilder->addCriteria(static::getPublishedCriteria())
     */
    protected static function getPublishedCriteria(?string $alias = 'p', \DateTimeInterface $refDate = null): Criteria
    {
        if (null === $refDate) {
            $refDate = new \DateTimeImmutable('now');
        }

        $prefix = $alias !== null && $alias !== '' ? $alias . '.' : '';

        $expr = Criteria::expr();

        return Criteria::create()
            ->andWhere(
                $expr->eq($prefix . 'isPublic', true)
            )->andWhere(
                $expr->andX(
                    $expr->orX(
                        $expr->isNull($prefix . 'dateScheduledFrom'),
                        $expr->lte($prefix . 'dateScheduledFrom', $refDate)
                    ),
                    $expr->orX(
                        $expr->isNull($prefix . 'dateScheduledTill'),
                        $expr->gte($prefix . 'dateScheduledTill', $refDate)
                    )
                )
            );
    }

    /**
     * @deprecated Use static call instead: static::getPublishedCriteria()
     */
    private function generatePublisedWhereClausesCriteria(string $alias = 'p', \DateTimeInterface $refDate = null): Criteria
    {
        trigger_deprecation('zicht/page-bundle', '6.1.5', 'Method "%s()" is deprecated. Use static call to "getPublishedCriteria()" instead.', __METHOD__);
        return static::getPublishedCriteria($alias, $refDate);
    }
}
