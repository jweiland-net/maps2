<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Domain\Repository;

use JWeiland\Maps2\Event\ModifyQueryOfFindPoiCollectionsEvent;
use JWeiland\Maps2\Helper\OverlayHelper;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository to collect poi collection records
 */
class PoiCollectionRepository extends Repository
{
    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING
    ];

    protected EventDispatcher $eventDispatcher;

    protected OverlayHelper $overlayHelper;

    public function injectOverlayHelper(OverlayHelper $overlayHelper): void
    {
        $this->overlayHelper = $overlayHelper;
    }

    public function injectEventDispatcher(EventDispatcher $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function findPoiCollections(array $settings, int $poiCollectionUid = 0): QueryResultInterface
    {
        $extbaseQuery = $this->createQuery();
        $queryBuilder = $this->getQueryBuilderForTable('tx_maps2_domain_model_poicollection', 'pc');
        $queryBuilder->select('*');

        $poiCollectionUid = $poiCollectionUid ?: (int)$settings['poiCollection'];
        if ($poiCollectionUid !== 0) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    'pc.uid',
                    $queryBuilder->createNamedParameter($poiCollectionUid, \PDO::PARAM_INT)
                )
            );
        } elseif (array_key_exists('categories', $settings) && $settings['categories'] !== '') {
            $this->addConstraintForCategories(
                $queryBuilder,
                GeneralUtility::intExplode(',', $settings['categories'], true)
            );
        }

        $this->eventDispatcher->dispatch(
            new ModifyQueryOfFindPoiCollectionsEvent(
                $queryBuilder,
                $settings,
                $poiCollectionUid
            )
        );

        return $extbaseQuery->statement($queryBuilder)->execute();
    }

    public function searchWithinRadius(float $latitude, float $longitude, int $radius): QueryResultInterface
    {
        $radiusOfEarth = 6380;

        /** @var Query $query */
        $query = $this->createQuery();
        $sql = '
            SELECT *, ACOS(SIN(RADIANS(?)) * SIN(RADIANS(latitude)) + COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(?) - RADIANS(longitude))) * ? AS distance
            FROM tx_maps2_domain_model_poicollection
            WHERE tx_maps2_domain_model_poicollection.pid IN (' . implode(',', $query->getQuerySettings()->getStoragePageIds()) . ')' .
            $this->getPageRepository()->enableFields('tx_maps2_domain_model_poicollection') . '
            HAVING distance < ?
            ORDER BY distance;';

        return $query->statement(
            $sql,
            [$latitude, $latitude, $longitude, $radiusOfEarth, $radius]
        )->execute();
    }

    public function findPoisByCategories($categories): QueryResultInterface
    {
        $query = $this->createQuery();
        $orConstraint = [];
        foreach (GeneralUtility::trimExplode(',', $categories) as $category) {
            $orConstraint[] = $query->contains('categories', $category);
        }

        return $query->matching(
            $query->logicalOr($orConstraint)
        )->execute();
    }

    protected function addConstraintForCategories(
        QueryBuilder $queryBuilder,
        array $categories
    ): void {
        $queryBuilder->leftJoin(
            'pc',
            'sys_category_record_mm',
            'category_mm',
            (string)$queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq(
                    'pc.uid',
                    $queryBuilder->quoteIdentifier('category_mm.uid_foreign')
                ),
                $queryBuilder->expr()->eq(
                    'category_mm.tablenames',
                    $queryBuilder->createNamedParameter(
                        'tx_maps2_domain_model_poicollection'
                    )
                ),
                $queryBuilder->expr()->eq(
                    'category_mm.fieldname',
                    $queryBuilder->createNamedParameter(
                        'categories'
                    )
                )
            )
        );

        $queryBuilder->andWhere(
            $queryBuilder->expr()->in(
                'category_mm.uid_local',
                $queryBuilder->createNamedParameter(
                    $categories,
                    Connection::PARAM_INT_ARRAY
                )
            )
        );
    }

    protected function getQueryBuilderForTable(string $table, string $alias, bool $useLangStrict = false): QueryBuilder
    {
        $extbaseQuery = $this->createQuery();

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($table);
        $queryBuilder->setRestrictions(GeneralUtility::makeInstance(FrontendRestrictionContainer::class));
        $queryBuilder
            ->from($table, $alias)
            ->andWhere(
                $queryBuilder->expr()->in(
                    'pid',
                    $queryBuilder->createNamedParameter(
                        $extbaseQuery->getQuerySettings()->getStoragePageIds(),
                        Connection::PARAM_INT_ARRAY
                    )
                )
            );

        $this->overlayHelper->addWhereForOverlay($queryBuilder, $table, $alias, $useLangStrict);

        return $queryBuilder;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
