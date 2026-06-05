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
use Doctrine\DBAL\Schema\Column;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Event\ModifyQueryOfFindPoiCollectionsEvent;
use JWeiland\Maps2\Helper\OverlayHelper;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
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

    private const EARTH_RADIUS = 6380;

    protected $defaultOrderings = [
        'title' => QueryInterface::ORDER_ASCENDING,
    ];

    public function __construct(
        protected ConnectionPool $connectionPool,
        protected DataMapper $dataMapper,
        protected EventDispatcherInterface $eventDispatcher,
        protected OverlayHelper $overlayHelper,
        protected Typo3DbQueryParser $typo3DbQueryParser,
    ) {
        parent::__construct();
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

    /**
     * Searches POI collections within the given radius.
     *
     * The query uses a calculated "distance" column in HAVING. This works for
     * the regular query, but Fluid checks like <f:if condition="{poiCollections}">
     * trigger a COUNT(*) query. Extbase removes the SELECT clause for that query,
     * so the calculated "distance" column is no longer available, and the query
     * fails.
     *
     * Returning plain, versioned, and translated records makes Fluid call
     * count(poiCollections) instead of poiCollections->count(). The records are
     * then mapped to objects manually.
     *
     * @return array<PoiCollection>
     */
    public function searchWithinRadius(float $latitude, float $longitude, int $radius): array
    {
        /** @var Query $extbaseQuery */
        $extbaseQuery = $this->createQuery();
        $queryBuilder = $this->typo3DbQueryParser->convertQueryToDoctrineQueryBuilder($extbaseQuery);
        $queryBuilder
            ->selectLiteral('*', 'ACOS(SIN(RADIANS(?)) * SIN(RADIANS(latitude)) + COS(RADIANS(?)) * COS(RADIANS(latitude)) * COS(RADIANS(?) - RADIANS(longitude))) * ? AS distance')
            ->having('distance < ?')
            ->orderBy('distance', 'ASC')
            ->setParameters([$latitude, $latitude, $longitude, self::EARTH_RADIUS, $radius]);

        $poiCollections = $extbaseQuery->statement($queryBuilder)->execute(true);

        return $this->dataMapper->map(PoiCollection::class, $poiCollections);
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

    /**
     * Returns all fully qualified column names of the POI collection table.
     *
     * The generated column list is used for both SELECT and GROUP BY clauses.
     * This is required for database systems with ONLY_FULL_GROUP_BY enabled,
     * where every selected non-aggregated column must also be part of the
     * GROUP BY clause.
     *
     * @return list<string>
     */
    protected function getColumnsForPoiCollectionTable(): array
    {
        $connection = $this->connectionPool->getConnectionForTable(self::TABLE);

        $columnNames = $connection->getSchemaInformation()->listTableColumnNames(self::TABLE);

        return array_map(
            static fn(string $columnName): string => self::TABLE . '.' . $columnName,
            $columnNames,
        );
    }
}
