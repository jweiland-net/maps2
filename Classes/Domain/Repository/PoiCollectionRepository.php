<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Domain\Repository;

use Doctrine\DBAL\Exception;
use JWeiland\Maps2\Event\ModifyQueryOfFindPoiCollectionsEvent;
use JWeiland\Maps2\Helper\OverlayHelper;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Query;
use TYPO3\CMS\Extbase\Persistence\Generic\Storage\Typo3DbQueryParser;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository to collect poi collection records
 */
class PoiCollectionRepository extends Repository
{
    private const TABLE = 'tx_maps2_domain_model_poicollection';

    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    protected EventDispatcherInterface $eventDispatcher;

    protected OverlayHelper $overlayHelper;

    protected Typo3DbQueryParser $typo3DbQueryParser;

    public function injectOverlayHelper(OverlayHelper $overlayHelper): void
    {
        $this->overlayHelper = $overlayHelper;
    }

    public function injectTypo3DbQueryParser(Typo3DbQueryParser $typo3DbQueryParser): void
    {
        $this->typo3DbQueryParser = $typo3DbQueryParser;
    }

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function findPoiCollections(array $settings, int $poiCollectionUid = 0): QueryResultInterface
    {
        $extbaseQuery = $this->createQuery();
        $queryBuilder = $this->typo3DbQueryParser->convertQueryToDoctrineQueryBuilder($extbaseQuery);
        $queryBuilder->select(...$this->getColumnsForPoiCollectionTable());

        $poiCollectionUid = $poiCollectionUid ?: (int)($settings['poiCollection'] ?? 0);
        if ($poiCollectionUid !== 0) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    self::TABLE . '.uid',
                    $queryBuilder->createNamedParameter($poiCollectionUid, Connection::PARAM_INT),
                ),
            );
        } elseif (array_key_exists('categories', $settings) && $settings['categories'] !== '') {
            $this->addConstraintForCategories(
                $queryBuilder,
                GeneralUtility::intExplode(',', $settings['categories'], true),
            );
        }

        $this->eventDispatcher->dispatch(
            new ModifyQueryOfFindPoiCollectionsEvent(
                $queryBuilder,
                $settings,
                $poiCollectionUid,
            ),
        );

        return $extbaseQuery->statement($queryBuilder)->execute();
    }

    public function searchWithinRadius(float $latitude, float $longitude, int $radius): QueryResultInterface
    {
        $radiusOfEarth = 6380;

        /** @var Query $query */
        $query = $this->createQuery();

        // Can't use QueryBuilder here, as Extbase deleted full select for COUNT(*) which results
        // in an error because HAVING does not found "distance" then.
        $sql = '
            SELECT *, ACOS(SIN(RADIANS(?)) * SIN(RADIANS(latitude)) + COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(?) - RADIANS(longitude))) * ? AS distance
            FROM tx_maps2_domain_model_poicollection
            WHERE tx_maps2_domain_model_poicollection.pid IN (' . implode(',', $query->getQuerySettings()->getStoragePageIds()) . ')' .
            $this->getPageRepository()->enableFields('tx_maps2_domain_model_poicollection') . '
            HAVING distance < ?
            ORDER BY distance;';

        return $query->statement(
            $sql,
            [$latitude, $latitude, $longitude, $radiusOfEarth, $radius],
        )->execute();
    }

    protected function addConstraintForCategories(QueryBuilder $queryBuilder, array $categories): void
    {
        $queryBuilder->leftJoin(
            self::TABLE,
            'sys_category_record_mm',
            'category_mm',
            (string)$queryBuilder->expr()->and(
                $queryBuilder->expr()->eq(
                    self::TABLE . '.uid',
                    $queryBuilder->quoteIdentifier('category_mm.uid_foreign'),
                ),
                $queryBuilder->expr()->eq(
                    'category_mm.tablenames',
                    $queryBuilder->createNamedParameter(
                        'tx_maps2_domain_model_poicollection',
                    ),
                ),
                $queryBuilder->expr()->eq(
                    'category_mm.fieldname',
                    $queryBuilder->createNamedParameter(
                        'categories',
                    ),
                ),
            ),
        );

        $queryBuilder->andWhere(
            $queryBuilder->expr()->in(
                'category_mm.uid_local',
                $queryBuilder->createNamedParameter(
                    $categories,
                    Connection::PARAM_INT_ARRAY,
                ),
            ),
        );

        $queryBuilder->addGroupBy(...$this->getColumnsForPoiCollectionTable());
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
                        Connection::PARAM_INT_ARRAY,
                    ),
                ),
            );

        $this->overlayHelper->addWhereForOverlay($queryBuilder, $table, $alias, $useLangStrict);

        return $queryBuilder;
    }

    /**
     * ->select() and ->groupBy() has to be the same in DB configuration
     * where only_full_group_by is activated.
     */
    protected function getColumnsForPoiCollectionTable(): array
    {
        $columns = [];
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_maps2_domain_model_poicollection');

        try {
            $schemaManager = $connection->createSchemaManager();
            $columns = array_map(
                static fn($column): string => self::TABLE . '.' . $column,
                array_keys(
                    $schemaManager->listTableColumns('tx_maps2_domain_model_poicollection') ?? [],
                ),
            );
        } catch (Exception) {
        }

        return $columns;
    }

    protected function getPageRepository(): PageRepository
    {
        return GeneralUtility::makeInstance(PageRepository::class);
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
