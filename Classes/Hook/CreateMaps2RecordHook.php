<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Hook;

/*
 * This file is part of the maps2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Helper\AddressHelper;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Helper\StoragePidHelper;
use JWeiland\Maps2\Service\GeoCodeService;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * Create a POI collection record while a foreign table was saved
 */
class CreateMaps2RecordHook
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var GeoCodeService
     */
    protected $geoCodeService;

    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    /**
     * @var FrontendInterface
     */
    protected $maps2RegistryCache;

    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * @var array
     */
    protected $columnRegistry = [];

    public function __construct(
        GeoCodeService $geoCodeService = null,
        MessageHelper $messageHelper = null,
        Dispatcher $signalSlotDispatcher = null,
        FrontendInterface $maps2RegistryCache = null
    ) {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        if ($geoCodeService === null) {
            $geoCodeService = $this->objectManager->get(GeoCodeService::class);
        }
        $this->geoCodeService = $geoCodeService;

        if ($messageHelper === null) {
            $messageHelper = GeneralUtility::makeInstance(MessageHelper::class);
        }
        $this->messageHelper = $messageHelper;

        if ($signalSlotDispatcher === null) {
            $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        }
        $this->signalSlotDispatcher = $signalSlotDispatcher;

        if ($maps2RegistryCache === null) {
            $maps2RegistryCache = $this->objectManager
                ->get(CacheManager::class)
                ->getCache('maps2_registry');
        }
        $this->maps2RegistryCache = $maps2RegistryCache;
        $this->columnRegistry = $this->maps2RegistryCache->get('fields') ?: [];
    }

    /**
     * Create a POI collection record while a foreign table was saved
     *
     * @param DataHandler $dataHandler
     * @throws \Exception
     */
    public function processDatamap_afterAllOperations(DataHandler $dataHandler)
    {
        foreach ($dataHandler->datamap as $foreignTableName => $recordsFromRequest) {
            if ($foreignTableName === 'tx_maps2_domain_model_poicollection') {
                $this->clearCacheForPoiCollectionRecords($recordsFromRequest);
                continue;
            }

            // process this hook only on registered tables
            if (!array_key_exists($foreignTableName, $this->columnRegistry)) {
                continue;
            }

            foreach ($recordsFromRequest as $uid => $recordFromRequest) {
                $foreignLocationRecord = $this->getForeignLocationRecord(
                    $foreignTableName,
                    $this->getRealUid($uid, $dataHandler)
                );
                if (empty($foreignLocationRecord)) {
                    continue;
                }

                foreach ($this->columnRegistry[$foreignTableName] as $foreignColumnName => $options) {
                    if (!array_key_exists($foreignColumnName, $foreignLocationRecord)) {
                        continue;
                    }

                    // Do not update foreign record automatically
                    // There are still extensions out there, where you want to define POI collection record on your own.
                    if (empty($options)) {
                        continue;
                    }

                    if (!$this->isForeignLocationRecordAllowedToCreateNewPoiCollectionRecords($foreignLocationRecord, $foreignTableName, $foreignColumnName, $options)) {
                        // We need $option of second foreach for this call. So, if this is false, we have to continue parent foreach.
                        continue 2;
                    }

                    $this->updateForeignLocationRecordIfPoiCollectionDoesNotExist($foreignLocationRecord, $foreignColumnName);

                    if (!$foreignLocationRecord[$foreignColumnName]) {
                        if ($this->createNewMapsRecord($foreignLocationRecord, $foreignTableName, $foreignColumnName, $options)) {
                            $this->synchronizeColumnsFromForeignRecordWithPoiCollection($foreignLocationRecord, $foreignTableName, $foreignColumnName, $options);
                            $this->messageHelper->addFlashMessage(
                                'While creating this record, we have automatically inserted a new maps2 record, too',
                                'Maps2 record creation successful',
                                FlashMessage::OK
                            );
                        }
                    } else {
                        $this->updateAddressInPoiCollectionIfNecessary($foreignLocationRecord, $foreignColumnName, $options);
                        $this->synchronizeColumnsFromForeignRecordWithPoiCollection($foreignLocationRecord, $foreignTableName, $foreignColumnName, $options);
                        $this->messageHelper->addFlashMessage(
                            'While updating this record, we have automatically updated the related maps2 record, too',
                            'Maps2 record update successful',
                            FlashMessage::OK
                        );
                    }
                    $this->emitPostUpdatePoiCollectionSignal(
                        'tx_maps2_domain_model_poicollection',
                        (int)$foreignLocationRecord[$foreignColumnName],
                        $foreignTableName,
                        $foreignLocationRecord,
                        $options
                    );
                    $this->clearHtmlCache((int)$foreignLocationRecord[$foreignColumnName]);
                }
            }
        }
    }

    /**
     * Check, if only a subset of records like pid=12 is allowed to create new PoiCollection records.
     * Further you can change behaviour with your own signal.
     *
     * @param array $foreignLocationRecord
     * @param string $foreignTableName
     * @param string $foreignColumnName
     * @param array $options
     * @return bool
     */
    protected function isForeignLocationRecordAllowedToCreateNewPoiCollectionRecords(
        array $foreignLocationRecord,
        string $foreignTableName,
        string $foreignColumnName,
        array $options
    ): bool {
        $isValid = true;

        // Process simple matching
        if (
            isset($options['columnMatch'])
            && is_array($options['columnMatch'])
        ) {
            foreach ($options['columnMatch'] as $columnName => $configuration) {
                $foreignValue = (string)$foreignLocationRecord[$columnName];
                if (empty($configuration)) {
                    continue;
                }
                if (
                    is_array($configuration)
                    && array_key_exists('expr', $configuration)
                    && array_key_exists('value', $configuration)
                ) {
                    switch ($configuration['expr']) {
                        case 'eq':
                            if ($foreignValue !== (string)$configuration['value']) {
                                $isValid = false;
                            }
                            break;
                        case 'lt':
                            if (!((int)$foreignValue < (int)$configuration['value'])) {
                                $isValid = false;
                            }
                            break;
                        case 'lte':
                            if (!((int)$foreignValue <= (int)$configuration['value'])) {
                                $isValid = false;
                            }
                            break;
                        case 'gt':
                            if (!((int)$foreignValue > (int)$configuration['value'])) {
                                $isValid = false;
                            }
                            break;
                        case 'gte':
                            if (!((int)$foreignValue >= (int)$configuration['value'])) {
                                $isValid = false;
                            }
                            break;
                        case 'in':
                        default:
                            if (!in_array(
                                $foreignValue,
                                GeneralUtility::trimExplode(',', $configuration['value'], true),
                                true
                            )) {
                                $isValid = false;
                            }
                            break;
                    }
                } elseif (!is_array($configuration) && array_key_exists($columnName, $foreignLocationRecord)) {
                    // $configuration is the value to check against. equals.
                    if ($foreignValue !== (string)$configuration) {
                        $isValid = false;
                        break;
                    }
                }
            }
        }

        // You need JOINs or more complex matches? Please register SignalSlot.
        $this->emitIsRecordAllowedToCreatePoiCollection(
            $foreignLocationRecord,
            $foreignTableName,
            $foreignColumnName,
            $options,
            $isValid
        );

        return $isValid;
    }

    /**
     * Clear InfoWindowContent Cache for our own PoiCollection records, too
     *
     * @param array $poiCollections
     */
    protected function clearCacheForPoiCollectionRecords(array $poiCollections)
    {
        foreach ($poiCollections as $uid => $poiCollection) {
            // Clear InfoWindowContent Cache for translation of record
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
     * @param int $poiCollectionUid
     */
    protected function clearHtmlCache(int $poiCollectionUid)
    {
        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('maps2_cachedhtml');
        $cache->flushByTag('infoWindowUid' . $poiCollectionUid);
    }

    /**
     * Sometimes the address may change in foreign location records.
     * We have to check for address changes.
     * If any, we have to query GeoCode again and update address in PoiCollection
     *
     * @param array $foreignLocationRecord
     * @param string $foreignColumnName
     * @param array $options
     */
    protected function updateAddressInPoiCollectionIfNecessary(array $foreignLocationRecord, string $foreignColumnName, array $options)
    {
        $addressHelper = GeneralUtility::makeInstance(AddressHelper::class);
        $poiCollection = $this->getPoiCollection((int)$foreignLocationRecord[$foreignColumnName]);
        if (!$addressHelper->isSameAddress($poiCollection['address'], $foreignLocationRecord, $options)) {
            $address = $addressHelper->getAddress($foreignLocationRecord, $options);

            $position = $this->geoCodeService->getFirstFoundPositionByAddress($address);
            if ($position instanceof Position) {
                $connection = $this->getConnectionPool()->getConnectionForTable('tx_maps2_domain_model_poicollection');
                $connection->update(
                    'tx_maps2_domain_model_poicollection',
                    [
                        'latitude' => $position->getLatitude(),
                        'longitude' => $position->getLongitude(),
                        'address' => $position->getFormattedAddress()
                    ],
                    [
                        'uid' => (int)$foreignLocationRecord[$foreignColumnName]
                    ]
                );
            }
        }
    }

    /**
     * If a related poi collection record was removed, the UID of this record will still stay in $foreignLocationRecord.
     * This method checks, if this UID is still valid. If not, we will remove this invalid relation from
     * $foreignLocationRecord.
     *
     * @param array $foreignLocationRecord
     * @param string $foreignColumnName
     */
    protected function updateForeignLocationRecordIfPoiCollectionDoesNotExist(array &$foreignLocationRecord, string $foreignColumnName)
    {
        $poiCollection = $this->getPoiCollection((int)$foreignLocationRecord[$foreignColumnName], ['uid']);
        if (empty($poiCollection)) {
            // record does not exist anymore. Remove it from relation
            $foreignLocationRecord[$foreignColumnName] = 0;
        }
    }

    /**
     * Get PoiCollection
     *
     * @param int $poiCollectionUid
     * @param array $columnsToSelect
     * @return array
     */
    protected function getPoiCollection(int $poiCollectionUid, array $columnsToSelect = ['*']): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_maps2_domain_model_poicollection');
        $queryBuilder->getRestrictions()->removeAll()->add(
            GeneralUtility::makeInstance(DeletedRestriction::class)
        );
        $poiCollection = $queryBuilder
            ->select(...$columnsToSelect)
            ->from('tx_maps2_domain_model_poicollection')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($poiCollectionUid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        if ($poiCollection === false) {
            $poiCollection = [];
        }

        return $poiCollection;
    }

    /**
     * While saving a location record, we automatically create a new poiCollection
     * record and set them into relation.
     *
     * @param array $foreignLocationRecord
     * @param string $foreignTableName
     * @param string $foreignColumnName
     * @param array $options
     * @return bool
     */
    protected function createNewMapsRecord(array &$foreignLocationRecord, string $foreignTableName, string $foreignColumnName, array $options): bool
    {
        $storagePidHelper = GeneralUtility::makeInstance(StoragePidHelper::class);
        $defaultStoragePid = $storagePidHelper->getDefaultStoragePidForNewPoiCollection($foreignLocationRecord, $options);
        if (empty($defaultStoragePid)) {
            return false;
        }

        $addressHelper = GeneralUtility::makeInstance(AddressHelper::class);
        $address = $addressHelper->getAddress($foreignLocationRecord, $options);
        var_dump($address);

        $position = $this->geoCodeService->getFirstFoundPositionByAddress($address);
        if ($position instanceof Position) {
            $mapService = GeneralUtility::makeInstance(MapService::class);
            $mapService->assignPoiCollectionToForeignRecord(
                $mapService->createNewPoiCollection($defaultStoragePid, $position),
                $foreignLocationRecord,
                $foreignTableName,
                $foreignColumnName
            );
            return true;
        }
        $this->messageHelper->addFlashMessage(
            'While saving this record, we tried to automatically create a new maps2 record, but Map Providers GeoCode API can not find your address: ' . $address,
            'Map Provider has not found your address',
            FlashMessage::ERROR
        );
        return false;
    }

    /**
     * Get location record of foreign extension, where our maps2 column (tx_maps2_uid) exists.
     * The record we try to fetch, is the record which the user has just saved. So this method should always find
     * this record.
     *
     * @param string $foreignTableName
     * @param int $uid
     * @return array
     */
    protected function getForeignLocationRecord(string $foreignTableName, int $uid): array
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($foreignTableName);
        $queryBuilder->getRestrictions()->removeAll()->add(
            GeneralUtility::makeInstance(DeletedRestriction::class)
        );

        $foreignLocationRecord = $queryBuilder
            ->select('*')
            ->from($foreignTableName)
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT)
                )
            )
            ->execute()
            ->fetch();

        if (empty($foreignLocationRecord)) {
            $foreignLocationRecord = [];
        }

        return $foreignLocationRecord;
    }

    /**
     * If a record was new, its uid is not an int. It's a string starting with "NEW"
     * This method returns the real uid as int.
     *
     * @param int|string $uid If new, $uid can start with NEW.
     * @param DataHandler $dataHandler
     * @return int
     */
    protected function getRealUid($uid, DataHandler $dataHandler): int
    {
        if (GeneralUtility::isFirstPartOfStr($uid, 'NEW')) {
            $uid = $dataHandler->substNEWwithIDs[$uid];
        }
        return (int)$uid;
    }

    /**
     * Synchronize some columns from foreign record with new POI collection record
     *
     * @param array $foreignLocationRecord
     * @param string $foreignTableName
     * @param string $maps2ColumnName
     * @param array $columnOptions
     * @return bool
     */
    public function synchronizeColumnsFromForeignRecordWithPoiCollection(array $foreignLocationRecord, $foreignTableName, $maps2ColumnName, array $columnOptions = []): bool
    {
        if (!array_key_exists('synchronizeColumns', $columnOptions)) {
            $this->messageHelper->addFlashMessage(
                'There are no synchronizationColumns configured in your maps2 registration, so we are using the address as maps2 title',
                'Using address as record title',
                FlashMessage::INFO
            );
            return false;
        }

        // Initialize QueryBuilder
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable('tx_maps2_domain_model_poicollection');
        $queryBuilder = $queryBuilder
            ->update('tx_maps2_domain_model_poicollection')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($foreignLocationRecord[$maps2ColumnName], \PDO::PARAM_INT)
                )
            );

        $tableNeedsUpdate = false;
        foreach ($columnOptions['synchronizeColumns'] as $synchronizeColumns) {
            if (!$this->isValidSynchronizeConfiguration($synchronizeColumns, $foreignTableName)) {
                return false;
            }
            $queryBuilder = $queryBuilder->set(
                $synchronizeColumns['poiCollectionColumnName'],
                $foreignLocationRecord[$synchronizeColumns['foreignColumnName']]
            );
            $tableNeedsUpdate = true;
        }

        // Only execute query, if there are columns to update
        if ($tableNeedsUpdate) {
            $queryBuilder->execute();
        }

        return true;
    }

    /**
     * This method checks the synchronization options itself and if columns are configured in TCA
     *
     * @param array $synchronizeColumns
     * @param string $foreignTableName
     * @return bool
     */
    protected function isValidSynchronizeConfiguration(array $synchronizeColumns, string $foreignTableName): bool
    {
        // Check options itself
        if (
            !array_key_exists('foreignColumnName', $synchronizeColumns)
            || !array_key_exists('poiCollectionColumnName', $synchronizeColumns)
            || !is_string($synchronizeColumns['foreignColumnName'])
            || !is_string($synchronizeColumns['poiCollectionColumnName'])
        ) {
            $this->messageHelper->addFlashMessage(
                'Please check your Maps registration. The keys foreignColumnName and poiCollectionColumnName have to be set.',
                'Missing registration keys',
                FlashMessage::ERROR
            );
            return false;
        }

        // Check, if configured foreign columnName is valid in TCA
        $foreignColumnName = $synchronizeColumns['foreignColumnName'];
        if (
            !array_key_exists($foreignTableName, $GLOBALS['TCA'])
            || !array_key_exists($foreignColumnName, $GLOBALS['TCA'][$foreignTableName]['columns'])
            || !is_array($GLOBALS['TCA'][$foreignTableName]['columns'][$foreignColumnName]['config'])
        ) {
            $this->messageHelper->addFlashMessage(
                'Error while trying to synchronize columns of your record with maps2 record. It seems that "' . $foreignTableName . '" is not registered as table or "' . $foreignColumnName . '" is not a valid column in ' . $foreignTableName,
                'Missing table/column in TCA',
                FlashMessage::ERROR
            );
            return false;
        }

        return true;
    }

    /**
     * Use this signal, if you want to implement further modification to our POI collection record, while saving
     * a foreign location record.
     *
     * @param string $poiCollectionTableName
     * @param int $poiCollectionUid
     * @param string $foreignTableName
     * @param array $foreignLocationRecord
     * @param array $options
     */
    protected function emitPostUpdatePoiCollectionSignal(string $poiCollectionTableName, int $poiCollectionUid, string $foreignTableName, array $foreignLocationRecord, array $options)
    {
        $this->signalSlotDispatcher->dispatch(
            self::class,
            'postUpdatePoiCollection',
            [$poiCollectionTableName, $poiCollectionUid, $foreignTableName, $foreignLocationRecord, $options]
        );
    }

    /**
     * Use this signal, if you want to check, if record is allowed to create PoiCollections on your own.
     *
     * @param array $foreignLocationRecord
     * @param string $foreignTableName
     * @param string $foreignColumnName
     * @param array $options
     * @param bool $isValid Is Reference
     */
    protected function emitIsRecordAllowedToCreatePoiCollection(array $foreignLocationRecord, string $foreignTableName, string $foreignColumnName, array $options, bool &$isValid)
    {
        $this->signalSlotDispatcher->dispatch(
            self::class,
            'preIsRecordAllowedToCreatePoiCollection',
            [$foreignLocationRecord, $foreignTableName, $foreignColumnName, $options, $isValid]
        );
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
