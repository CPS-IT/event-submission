<?php
declare(strict_types=1);

/*
 * This file is part of the event_submission project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

namespace Cpsit\EventSubmission\Domain\Repository;

use Cpsit\EventSubmission\Domain\Model\Dto\DemandInterface;
use Cpsit\EventSubmission\Domain\Model\Job;
use Nng\Nnrestapi\Annotations\Example;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Event submission model repository
 */
class JobRepository extends Repository
{
    public function initializeObject()
    {
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }

    /**
     * Find submissions (Jobs) where the created event is past its end date.
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function findWithExpiredEvents(): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->lessThan('event.endDate', time());

        return $query->execute();
    }

    /**
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function deleteWithExpiredEvents(): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable(Job::TABLE_NAME);
        $queryBuilder = $connection->createQueryBuilder();

        // we want to remove jobs with hidden or un-published event too
        $restrictions = $queryBuilder->getRestrictions()->removeAll()->add(
            GeneralUtility::makeInstance(DeletedRestriction::class)
        );
        $queryBuilder->setRestrictions($restrictions);

        $toDelete = [];

        /**
         * Note: Doctrine does not support joints in update queries. Therefore, we have to
         * fetch them first and update them in a second query.
         */
        $expiredQuery = $queryBuilder->select('job.uid')
            ->from(Job::TABLE_NAME, 'job')
            ->leftJoin(
                'job',
                'tx_news_domain_model_news',
                'event',
                $queryBuilder->expr()->eq(
                    'event.uid', $queryBuilder->quoteIdentifier('job.event')
                )
            )
            ->where(
                $queryBuilder->expr()->lt(
                    'event.event_end',
                    time()
                )
            );
        $expiredRows = $expiredQuery->execute()->fetchAllAssociative();

        // gather uid for deletion
        foreach ($expiredRows as $item) {
            $toDelete[] = $item[Job::FIELD_UID];
        }

        if (empty($toDelete)) {
            return 0;
        }

        // soft delete
        $queryBuilder->update(Job::TABLE_NAME)
            ->set('deleted', 1)
            ->where(
                $queryBuilder->expr()->in(Job::FIELD_UID, $toDelete)
            );
        return $queryBuilder->execute();
    }

    public function findDemanded(DemandInterface $demand): QueryResultInterface
    {
        $query = $this->createQuery();

        $this->applyConstraints($query, $demand);
        $this->applyOrderings($query, $demand);
        $this->applyOffsetLimit($query, $demand);

        return $query->execute();
    }

    public function findByEventUid(int $uid): null|object
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);

        return $query
            ->matching(
                $query->logicalAnd(
                    $query->equals('event.uid', $uid),
                ),
            )
            ->execute()->getFirst();
    }

    protected function applyConstraints(QueryInterface $query, DemandInterface $demand): void
    {
        $constraints = [];

        if (!empty($pages = $demand->getPageIds())) {
            $querySettings = $query->getQuerySettings();
            $querySettings->setRespectStoragePage(true)
                ->setStoragePageIds($pages);
            $query->setQuerySettings($querySettings);
        }

        if (!empty($demand->getStatus())) {
            $constraints[] = $query->in(
                Job::FIELD_STATUS, $demand->getStatus()
            );
        }

        if (!empty($constraints)) {
            $query->matching(
                $query->logicalAnd(...$constraints)
            );
        }
    }

    protected function applyOffsetLimit(QueryInterface $query, DemandInterface $demand): void
    {
        if (!empty($demand->getLimit())) {
            $query->setLimit($demand->getLimit());
        }

        if (!empty($demand->getOffset())) {
            if (empty($query->getLimit())) {
                $query->setLimit(PHP_INT_MAX);
            }
            $query->setOffset((int)$demand->getOffset());
        }
    }

    /**
     * Orderings strings
     *
     * @Example(property desc, property asc)
     *
     * @param QueryInterface $query
     * @param DemandInterface $demand
     * @return array
     */
    protected function applyOrderings(QueryInterface $query, DemandInterface $demand): array
    {
        $orderings = [];
        $sorting = GeneralUtility::trimExplode(',', $demand->getSorting(), true);

        if (!empty($sorting)) {
            foreach ($sorting as $orderItem) {
                [$orderField, $ascDesc] = GeneralUtility::trimExplode(' ', $orderItem, true);
                // count == 1 means that no direction is given
                if ($ascDesc) {
                    $orderings[$orderField] = ((strtolower($ascDesc) === 'desc') ?
                        QueryInterface::ORDER_DESCENDING :
                        QueryInterface::ORDER_ASCENDING);
                } else {
                    $orderings[$orderField] = QueryInterface::ORDER_ASCENDING;
                }
            }
        }

        if (!empty($orderings)) {
            $query->setOrderings($orderings);
        }

        return $orderings;
    }

}
