<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Hook;

use Doctrine\DBAL\Exception;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Event\AllowCreationOfPoiCollectionEvent;
use JWeiland\Maps2\Event\PostProcessPoiCollectionRecordEvent;
use JWeiland\Maps2\Helper\AddressHelper;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Helper\StoragePidHelper;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use JWeiland\Maps2\Tca\ColumnRegistration;
use JWeiland\Maps2\Tca\ColumnRegistrationStorage;
use JWeiland\Maps2\Tca\SynchronizeColumn;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Schema\Capability\TcaSchemaCapability;
use TYPO3\CMS\Core\Schema\TcaSchemaFactory;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Create a POI collection record while a foreign table was saved
 */
final readonly class CreateMaps2RecordHook
{
    public function __construct(
        private GeoCodeService $geoCodeService,
        private AddressHelper $addressHelper,
        private MessageHelper $messageHelper,
        private StoragePidHelper $storagePidHelper,
        private MapService $mapService,
        private ColumnRegistrationStorage $columnRegistry,
        private EventDispatcherInterface $eventDispatcher,
        private TcaSchemaFactory $tcaSchemaFactory,
        private CacheManager $cacheManager,
        private ConnectionPool $connectionPool,
    ) {}

    /**
     * Create a POI collection record while a foreign table was saved
     */
    public function processDatamap_afterAllOperations(DataHandler $dataHandler): void
    {
        foreach ($dataHandler->datamap as $foreignTableName => $recordsFromRequest) {
            if ($foreignTableName === 'tx_maps2_domain_model_poicollection') {
                $this->clearCacheForPoiCollectionRecords($recordsFromRequest);
                continue;
            }

            // process this hook only on registered tables
            if (!$this->columnRegistry->isTableRegistered($foreignTableName)) {
                continue;
            }

            foreach ($recordsFromRequest as $uid => $recordFromRequest) {
                if (!$this->isValidRecord($recordFromRequest, $foreignTableName)) {
                    continue;
                }

                $foreignLocationRecord = $this->getForeignLocationRecord(
                    $foreignTableName,
                    $this->getRealUid($uid, $dataHandler),
                );
                if ($foreignLocationRecord === []) {
                    continue;
                }

                foreach ($this->columnRegistry->getColumnRegistrations($foreignTableName) as $foreignColumnName => $columnRegistration) {
                    if (!array_key_exists($foreignColumnName, $foreignLocationRecord)) {
                        continue;
                    }

                    if (!$this->isForeignLocationRecordAllowedToCreateNewPoiCollectionRecords($foreignLocationRecord, $foreignTableName, $foreignColumnName, $columnRegistration)) {
                        // We need the option of the second foreach for this call.
                        // So, if this is false, we have to continue parent foreach.
                        continue 2;
                    }

                    $this->updateForeignLocationRecordIfPoiCollectionDoesNotExist($foreignLocationRecord, $foreignColumnName);

                    if (!$foreignLocationRecord[$foreignColumnName]) {
                        if ($this->createNewMapsRecord($foreignLocationRecord, $foreignTableName, $foreignColumnName, $columnRegistration)) {
                            $this->synchronizeColumnsFromForeignRecordWithPoiCollection($foreignLocationRecord, $foreignTableName, $foreignColumnName, $columnRegistration);
                            $this->messageHelper->addFlashMessage(
                                'While creating this record, we have automatically inserted a new maps2 record, too',
                                'Maps2 record creation successful',
                            );
                        }
                    } else {
                        $this->updateAddressInPoiCollectionIfNecessary($foreignLocationRecord, $foreignColumnName, $columnRegistration);
                        $this->synchronizeColumnsFromForeignRecordWithPoiCollection($foreignLocationRecord, $foreignTableName, $foreignColumnName, $columnRegistration);
                        $this->messageHelper->addFlashMessage(
                            'While updating this record, we have automatically updated the related maps2 record, too',
                            'Maps2 record update successful',
                        );
                    }

                    $this->emitPostUpdatePoiCollectionEvent(
                        'tx_maps2_domain_model_poicollection',
                        (int)$foreignLocationRecord[$foreignColumnName],
                        $foreignTableName,
                        $foreignLocationRecord,
                        $columnRegistration,
                    );

                    $this->clearHtmlCache((int)$foreignLocationRecord[$foreignColumnName]);
                }
            }
        }
    }

    /**
     * TYPO3 adds parts of translated records to DataMap while saving a record in the default language.
     * See: DataMapProcessor::instance(x, y, z)->process(); in DataHandler::process_datamap().
     *
     * These translated records contain all columns configured with l10n_mode=exclude like "starttime" and "endtime".
     * As these translated records are processed at last, they will override the title of your connected
     * poiCollection records in the default language with the title of the last processed translated record.
     *
     * This method prevents processing such records.
     */
    private function isValidRecord(array $recordFromRequest, string $tableName): bool
    {
        if (!$this->tcaSchemaFactory->has($tableName)) {
            return false;
        }

        $schema = $this->tcaSchemaFactory->get($tableName);

        if (!$schema->hasCapability(TcaSchemaCapability::Language)) {
            return true;
        }

        $languageField = $schema
            ->getCapability(TcaSchemaCapability::Language)
            ->getLanguageField()
            ->getName();

        return array_key_exists($languageField, $recordFromRequest);
    }

    /**
     * Check if only a subset of records like pid=12 is allowed to create new PoiCollection records.
     * Further, you can change behavior with your own signal.
     */
    private function isForeignLocationRecordAllowedToCreateNewPoiCollectionRecords(
        array $foreignLocationRecord,
        string $foreignTableName,
        string $foreignColumnName,
        ColumnRegistration $columnRegistration,
    ): bool {
        $isValid = true;

        foreach ($columnRegistration->getColumnMatch() as $columnName => $columnMatch) {
            $foreignValue = (string)($foreignLocationRecord[$columnName] ?? '');

            switch ($columnMatch->getExpr()) {
                case 'eq':
                    if ($foreignValue !== $columnMatch->getValue()) {
                        $isValid = false;
                    }
                    break;
                case 'lt':
                    if ((int)$foreignValue >= (int)$columnMatch->getValue()) {
                        $isValid = false;
                    }
                    break;
                case 'lte':
                    if ((int)$foreignValue > (int)$columnMatch->getValue()) {
                        $isValid = false;
                    }
                    break;
                case 'gt':
                    if ((int)$foreignValue <= (int)$columnMatch->getValue()) {
                        $isValid = false;
                    }
                    break;
                case 'gte':
                    if ((int)$foreignValue < (int)$columnMatch->getValue()) {
                        $isValid = false;
                    }
                    break;
                case 'in':
                default:
                    if (!in_array(
                        $foreignValue,
                        GeneralUtility::trimExplode(',', $columnMatch->getValue(), true),
                        true,
                    )) {
                        $isValid = false;
                    }
                    break;
            }
        }

        // You need JOINs or more complex matches? Please register an Event.
        $this->emitIsRecordAllowedToCreatePoiCollection(
            $foreignLocationRecord,
            $foreignTableName,
            $foreignColumnName,
            $columnRegistration,
            $isValid,
        );

        return $isValid;
    }

    /**
     * Clear InfoWindowContent Cache for our own PoiCollection records, too
     */
    private function clearCacheForPoiCollectionRecords(array $poiCollections): void
    {
        foreach ($poiCollections as $uid => $poiCollection) {
            // Clear InfoWindowContent Cache to translate record
            if (MathUtility::canBeInterpretedAsInteger($uid)) {
                $this->clearHtmlCache((int)$uid);
            }

            // Clear InfoWindowContent Cache for original language of record
            $originalTranslationColumn = $GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['ctrl']['transOrigPointerField'];
            if (isset($poiCollection[$originalTranslationColumn])) {
                $this->clearHtmlCache((int)$poiCollection[$originalTranslationColumn]);
            }
        }
    }

    /**
     * After saving a PoiCollection the additional information RTE content may have changed.
     * As this content will be stored in our maps2_cachedhtml cache, we have to remove that entry after save.
     *
     * @see Fluid VH cache.setCache()
     */
    private function clearHtmlCache(int $poiCollectionUid): void
    {
        try {
            $this->cacheManager
                ->getCache('maps2_cachedhtml')
                ->flushByTag('infoWindowUid' . $poiCollectionUid);
        } catch (NoSuchCacheException) {
            // Do nothing
        }
    }

    /**
     * Sometimes the address may change in foreign location records.
     * We have to check for address changes.
     * If any, we have to query GeoCode again and update the address in PoiCollection
     */
    private function updateAddressInPoiCollectionIfNecessary(
        array $foreignLocationRecord,
        string $foreignColumnName,
        ColumnRegistration $columnRegistration,
    ): void {
        $poiCollection = $this->getPoiCollection((int)$foreignLocationRecord[$foreignColumnName]);
        if (!$this->addressHelper->isSameAddress($poiCollection['address'], $foreignLocationRecord, $columnRegistration)) {
            $address = $this->addressHelper->getAddress($foreignLocationRecord, $columnRegistration);

            $position = $this->geoCodeService->getFirstFoundPositionByAddress($address);
            if ($position instanceof Position) {
                $connection = $this->connectionPool->getConnectionForTable('tx_maps2_domain_model_poicollection');
                $connection->update(
                    'tx_maps2_domain_model_poicollection',
                    [
                        'latitude' => $position->getLatitude(),
                        'longitude' => $position->getLongitude(),
                        'address' => $position->getFormattedAddress(),
                    ],
                    [
                        'uid' => (int)$foreignLocationRecord[$foreignColumnName],
                    ],
                );
            }
        }
    }

    /**
     * If a related poi collection record was removed, the UID of this record will still stay in $foreignLocationRecord.
     * This method checks if this UID is still valid. If not, we will remove this invalid relation from
     * $foreignLocationRecord.
     */
    private function updateForeignLocationRecordIfPoiCollectionDoesNotExist(
        array &$foreignLocationRecord,
        string $foreignColumnName,
    ): void {
        $poiCollection = $this->getPoiCollection((int)$foreignLocationRecord[$foreignColumnName], ['uid']);
        if ($poiCollection === []) {
            // record does not exist anymore. Remove it from relation
            $foreignLocationRecord[$foreignColumnName] = 0;
        }
    }

    private function getPoiCollection(int $poiCollectionUid, array $columnsToSelect = ['*']): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_maps2_domain_model_poicollection');
        $queryBuilder->getRestrictions()->removeAll()->add(
            GeneralUtility::makeInstance(DeletedRestriction::class),
        );

        try {
            $poiCollection = $queryBuilder
                ->select(...$columnsToSelect)
                ->from('tx_maps2_domain_model_poicollection')
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($poiCollectionUid, Connection::PARAM_INT),
                    ),
                )
                ->executeQuery()
                ->fetchAssociative();
        } catch (Exception) {
            $poiCollection = false;
        }

        if ($poiCollection === false) {
            return [];
        }

        return $poiCollection;
    }

    /**
     * While saving a location record, we automatically create a new poiCollection
     * record and set them into relation.
     */
    private function createNewMapsRecord(
        array &$foreignLocationRecord,
        string $foreignTableName,
        string $foreignColumnName,
        ColumnRegistration $columnRegistration,
    ): bool {
        $defaultStoragePid = $this->storagePidHelper->getDefaultStoragePidForNewPoiCollection(
            $foreignLocationRecord,
            $columnRegistration,
        );
        if ($defaultStoragePid === 0) {
            return false;
        }

        $address = $this->addressHelper->getAddress($foreignLocationRecord, $columnRegistration);

        $position = $this->geoCodeService->getFirstFoundPositionByAddress($address);
        if ($position instanceof Position) {
            $this->mapService->assignPoiCollectionToForeignRecord(
                $this->mapService->createNewPoiCollection($defaultStoragePid, $position),
                $foreignLocationRecord,
                $foreignTableName,
                $foreignColumnName,
            );

            return true;
        }

        $this->messageHelper->addFlashMessage(
            'While saving this record, we tried to automatically create a new maps2 record, but Map Providers GeoCode API can not find your address: ' . $address,
            'Map Provider has not found your address',
            ContextualFeedbackSeverity::ERROR,
        );

        return false;
    }

    /**
     * Get a location record of a foreign extension, where our maps2 column (tx_maps2_uid) exists.
     * The record we try to fetch is the record which the user has just saved. So this method should always find
     * this record.
     */
    private function getForeignLocationRecord(string $foreignTableName, int $uid): array
    {
        if ($uid === 0) {
            return [];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($foreignTableName);
        $queryBuilder->getRestrictions()->removeAll()->add(
            GeneralUtility::makeInstance(DeletedRestriction::class),
        );

        try {
            $foreignLocationRecord = $queryBuilder
                ->select('*')
                ->from($foreignTableName)
                ->where(
                    $queryBuilder->expr()->eq(
                        'uid',
                        $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT),
                    ),
                )
                ->executeQuery()
                ->fetchAssociative();
        } catch (Exception) {
            $foreignLocationRecord = [];
        }

        if (empty($foreignLocationRecord)) {
            return [];
        }

        return $foreignLocationRecord;
    }

    /**
     * If a record was new, its uid is not an int. It's a string starting with "NEW"
     * This method returns the real uid as int.
     */
    private function getRealUid(int|string $uid, DataHandler $dataHandler): int
    {
        if (str_starts_with((string)$uid, 'NEW')) {
            $uid = $dataHandler->substNEWwithIDs[$uid] ?? 0;
        }

        return (int)$uid;
    }

    /**
     * Synchronize some columns from a foreign record with a new POI collection record
     */
    public function synchronizeColumnsFromForeignRecordWithPoiCollection(
        array $foreignLocationRecord,
        string $foreignTableName,
        string $maps2ColumnName,
        ColumnRegistration $columnRegistration,
    ): bool {
        if ($columnRegistration->getSynchronizeColumns() === []) {
            $this->messageHelper->addFlashMessage(
                'There are no synchronizationColumns configured in your maps2 registration, so we are using the address as maps2 title',
                'Using address as record title',
                ContextualFeedbackSeverity::INFO,
            );

            return false;
        }

        // Initialize QueryBuilder
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_maps2_domain_model_poicollection');
        $queryBuilder = $queryBuilder
            ->update('tx_maps2_domain_model_poicollection')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($foreignLocationRecord[$maps2ColumnName], Connection::PARAM_INT),
                ),
            );

        foreach ($columnRegistration->getSynchronizeColumns() as $synchronizeColumns) {
            if (!$this->isValidSynchronizeConfiguration($synchronizeColumns, $foreignTableName)) {
                return false;
            }

            $queryBuilder = $queryBuilder->set(
                $synchronizeColumns->getPoiCollectionColumnName(),
                $synchronizeColumns->getForeignColumn()->resolveValue($foreignLocationRecord),
            );
        }

        $queryBuilder->executeStatement();

        return true;
    }

    /**
     * This method checks the synchronization options itself and if columns are configured in TCA
     */
    private function isValidSynchronizeConfiguration(SynchronizeColumn $synchronizeColumns, string $foreignTableName): bool
    {
        // Check if every configured foreign columnName is valid in TCA
        foreach ($synchronizeColumns->getForeignColumn()->getColumnNames() as $foreignColumnName) {
            if (
                !array_key_exists($foreignTableName, $GLOBALS['TCA'])
                || !array_key_exists($foreignColumnName, $GLOBALS['TCA'][$foreignTableName]['columns'])
                || !is_array($GLOBALS['TCA'][$foreignTableName]['columns'][$foreignColumnName]['config'])
            ) {
                $this->messageHelper->addFlashMessage(
                    'Error while trying to synchronize columns of your record with maps2 record. It seems that "'
                    . $foreignTableName . '" is not registered as table or "'
                    . $foreignColumnName . '" is not a valid column in ' . $foreignTableName,
                    'Missing table/column in TCA',
                    ContextualFeedbackSeverity::ERROR,
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Use this event if you want to implement further modification to our POI collection record, while saving
     * a foreign location record.
     */
    private function emitPostUpdatePoiCollectionEvent(
        string $poiCollectionTableName,
        int $poiCollectionUid,
        string $foreignTableName,
        array $foreignLocationRecord,
        ColumnRegistration $columnRegistration,
    ): void {
        $this->eventDispatcher->dispatch(
            new PostProcessPoiCollectionRecordEvent(
                $poiCollectionTableName,
                $poiCollectionUid,
                $foreignTableName,
                $foreignLocationRecord,
                $columnRegistration,
            ),
        );
    }

    /**
     * Use this event if you want to check if record is allowed to create PoiCollections on your own.
     */
    private function emitIsRecordAllowedToCreatePoiCollection(
        array $foreignLocationRecord,
        string $foreignTableName,
        string $foreignColumnName,
        ColumnRegistration $columnRegistration,
        bool &$isValid,
    ): void {
        $event = new AllowCreationOfPoiCollectionEvent(
            $foreignLocationRecord,
            $foreignTableName,
            $foreignColumnName,
            $columnRegistration,
            $isValid,
        );

        /** @var AllowCreationOfPoiCollectionEvent $event */
        $event = $this->eventDispatcher->dispatch($event);
        $isValid = $event->isValid();
    }
}
