<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Update;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Install\Updates\ConfirmableInterface;
use TYPO3\CMS\Install\Updates\Confirmation;
use TYPO3\CMS\Install\Updates\DatabaseUpdatedPrerequisite;
use TYPO3\CMS\Install\Updates\UpgradeWizardInterface;

/**
 * With maps2 10.0.0 we have removed the poi table.
 * Use this Upgrade Wizard to migrate all poi records as JSON into the configuration_map column of poicollection record
 */
class MigratePoiRecordsToConfigurationMapUpdate implements UpgradeWizardInterface, ConfirmableInterface
{
    public function getIdentifier(): string
    {
        return 'maps2MigratePoiRecord';
    }

    public function getTitle(): string
    {
        return '[maps2] Migrate all POI records as JSON into poicollection record';
    }

    public function getDescription(): string
    {
        return 'We have simplified the POI handling a lot and removed table tx_maps2_domain_model_poi. ' .
            'All POIs will now be stored in configuration_map of table tx_maps2_domain_model_poicollection as JSON.';
    }

    public function getConfirmation(): Confirmation
    {
        return new Confirmation(
            'Have you changed column "configuration_map" to be of type TEXT?',
            'This UpgradeWizards needs column "configuration_map" of table "tx_maps2_domain_model_poicollection" ' .
            'to be of type TEXT in database. Else it may happen that POIs will be stored as incomplete JSON string ' .
            'in configuration_map column. Further table "tx_maps2_domain_model_poi" should not be deleted.',
            false
        );
    }

    public function updateNecessary(): bool
    {
        // Something with DB was gone totally wrong. Skip Upgrade. Repair your DB first.
        try {
            $connection = $this
                ->getConnectionPool()
                ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        } catch (Exception $exception) {
            return false;
        }

        // If table "tx_maps2_domain_model_poi" was already removed, skip this upgrade
        $schemaManager = $connection->getSchemaManager();
        if (
            $schemaManager instanceof AbstractSchemaManager
            && !$schemaManager->tablesExist(['tx_maps2_domain_model_poi'])
        ) {
            return false;
        }

        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_maps2_domain_model_poicollection');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        return (bool)$queryBuilder
            ->select('*')
            ->from('tx_maps2_domain_model_poicollection', 'pc')
            ->leftJoin(
                'pc',
                'tx_maps2_domain_model_poi',
                'p',
                $queryBuilder->expr()->eq(
                    'pc.uid',
                    $queryBuilder->quoteIdentifier('p.poicollection')
                )
            )
            ->where(
                $queryBuilder->expr()->isNotNull(
                    'p.pid'
                )
            )
            ->orWhere(
                $queryBuilder->expr()->eq(
                    'collection_type',
                    $queryBuilder->createNamedParameter('Area')
                ),
                $queryBuilder->expr()->eq(
                    'collection_type',
                    $queryBuilder->createNamedParameter('Route')
                )
            )
            ->execute()
            ->fetchColumn();
    }

    public function executeUpdate(): bool
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_maps2_domain_model_poicollection');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $statement = $queryBuilder
            ->select('uid')
            ->from('tx_maps2_domain_model_poicollection')
            ->execute();

        while ($poiCollectionRecord = $statement->fetch()) {
            $connection = $this->getConnectionPool()->getConnectionForTable('tx_maps2_domain_model_poi');

            $connection->update(
                'tx_maps2_domain_model_poicollection',
                [
                    'configuration_map' => json_encode(
                        $this->migratePoiRecords(
                            $poiCollectionRecord['uid']
                        ),
                        JSON_THROW_ON_ERROR
                    )
                ],
                [
                    'uid' => (int)$poiCollectionRecord['uid']
                ]
            );
        }

        return true;
    }

    /**
     * @return array<int|string, string>
     */
    protected function migratePoiRecords(int $poiCollectionUid): array
    {
        $routes = [];
        foreach ($this->getPoiRecords($poiCollectionUid) as $poiRecord) {
            $routes[$poiRecord['pos_index']] = $poiRecord['latitude'] . ',' . $poiRecord['longitude'];
        }

        return $routes;
    }

    /**
     * @return mixed[]
     */
    protected function getPoiRecords(int $poiCollectionUid): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_maps2_domain_model_poi');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $statement = $queryBuilder
            ->select('uid', 'pos_index', 'latitude', 'longitude')
            ->from('tx_maps2_domain_model_poi')
            ->where(
                $queryBuilder->expr()->eq(
                    'poicollection',
                    $queryBuilder->createNamedParameter($poiCollectionUid, \PDO::PARAM_INT)
                )
            )
            ->execute();

        $poiRecords = [];
        while ($poiRecord = $statement->fetch()) {
            $poiRecords[] = $poiRecord;
        }

        return $poiRecords;
    }

    /**
     * @return array<class-string<DatabaseUpdatedPrerequisite>>
     */
    public function getPrerequisites(): array
    {
        return [
            DatabaseUpdatedPrerequisite::class
        ];
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
