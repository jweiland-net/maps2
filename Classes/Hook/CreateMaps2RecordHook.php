<?php

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

use JWeiland\Maps2\Domain\Model\RadiusResult;
use JWeiland\Maps2\Helper\AddressHelper;
use JWeiland\Maps2\Service\GoogleMapsService;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * Create a maps2 record while a foreign table was saved
 */
class CreateMaps2RecordHook
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var GoogleMapsService
     */
    protected $googleMapsService;

    /**
     * @var FlashMessageService
     */
    protected $flashMessageService;

    /**
     * @var Registry
     */
    protected $sysRegistry;

    /**
     * @var array
     */
    protected $columnRegistry = [];

    /**
     * DataHandlerHook constructor.
     */
    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->googleMapsService = $this->objectManager->get(GoogleMapsService::class);
        $this->flashMessageService = $this->objectManager->get(FlashMessageService::class);
        $this->sysRegistry = $this->objectManager->get(Registry::class);
        $this->columnRegistry = $this->sysRegistry->get('maps2_registry', 'fields') ?: [];
    }

    /**
     * Try to find a similar poiCollection. If found connect it with current record.
     *
     * @param string $status "new" od something else to update the record
     * @param string $foreignTableName The foreign table name
     * @param int $uid The UID of the new or updated record. Can be prepended with NEW if record is new. Use: $this->substNEWwithIDs to convert
     * @param array $fieldArray The fields of the current record
     * @param DataHandler $dataHandler
     * @return void
     * @throws \Exception
     */
    public function processDatamap_afterDatabaseOperations($status, $foreignTableName, $uid, array $fieldArray, $dataHandler)
    {
        // process this hook only on registered tables
        if (!array_key_exists($foreignTableName, $this->columnRegistry)) {
            return;
        }

        $foreignLocationRecord = $this->getForeignLocationRecord(
            $foreignTableName,
            $this->getRealUid($uid, $dataHandler)
        );
        if (empty($foreignLocationRecord)) {
            return;
        }

        foreach ($this->columnRegistry[$foreignTableName] as $foreignColumnName => $options) {
            if (!array_key_exists($foreignColumnName, $foreignLocationRecord)) {
                continue;
            }

            if (!$foreignLocationRecord[$foreignColumnName]) {
                if ($this->createNewMapsRecord($foreignLocationRecord, $foreignTableName, $foreignColumnName, $options)) {
                    $this->synchronizeColumnsFromForeignRecordWithPoiCollection($foreignLocationRecord, $foreignTableName, $foreignColumnName, $options);
                    $this->addMessage(
                        'While saving this record, we have automatically inserted a new maps2 record, too',
                        'Maps2 record creation successful',
                        FlashMessage::OK
                    );
                }
            } else {
                $this->synchronizeColumnsFromForeignRecordWithPoiCollection($foreignLocationRecord, $foreignTableName, $foreignColumnName, $options);
                $this->addMessage(
                    'While saving this record, we have automatically updated the related maps2 record, too',
                    'Maps2 record update successful',
                    FlashMessage::OK
                );
            }
        }
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
        $defaultStoragePid = $this->getDefaultStoragePidForNewPoiCollections($foreignLocationRecord, $options);
        if (empty($defaultStoragePid)) {
            return false;
        }

        $addressHelper = GeneralUtility::makeInstance(AddressHelper::class);
        $address = $addressHelper->getAddress($foreignLocationRecord, $options);

        $radiusResult = $this->googleMapsService->getFirstFoundPositionByAddress($address);
        if ($radiusResult instanceof RadiusResult) {
            $this->googleMapsService->assignPoiCollectionToForeignRecord(
                $this->googleMapsService->createNewPoiCollection($defaultStoragePid, $radiusResult),
                $foreignLocationRecord,
                $foreignTableName,
                $foreignColumnName
            );
            return true;
        }
        $this->addMessage(
            'While saving this record, we tried to automatically create a new maps2 record, but Google GeoCode API can not find your address: ' . $address,
            'Google has not found your address',
            FlashMessage::ERROR
        );
        return false;
    }

    /**
     * Check various sources to get a default storage PID for new POI Collection records
     *
     * @param array $foreignLocationRecord
     * @param array $options
     * @return int
     */
    protected function getDefaultStoragePidForNewPoiCollections(array $foreignLocationRecord, array $options)
    {
        $defaultStoragePid = 0;

        // Low Priority: Get PID from foreignLocationRecord
        if (
            array_key_exists('pid', $foreignLocationRecord)
            && MathUtility::canBeInterpretedAsInteger($foreignLocationRecord['pid'])
        ) {
            $defaultStoragePid = (int)$foreignLocationRecord['pid'];
        }

        // Mid Priority: Get PID from Maps2 Registration
        if (array_key_exists('defaultStoragePid', $options)) {
            $storagePid = 0;
            // Very bad idea, as PID will be hardcoded in foreign extension source code
            if (
                !is_array($options['defaultStoragePid'])
                && MathUtility::canBeInterpretedAsInteger($options['defaultStoragePid'])
                && (int)$options['defaultStoragePid'] > 0
            ) {
                $storagePid = (int)$options['defaultStoragePid'];
            }

            // A way better idea: Use a configuration to access ExtensionManager configuration of foreign extension
            if (
                is_array($options['defaultStoragePid'])
                && array_key_exists('extKey', $options['defaultStoragePid'])
                && !empty($options['defaultStoragePid']['extKey'])
                && array_key_exists('property', $options['defaultStoragePid'])
                && !empty($options['defaultStoragePid']['property'])
                && ExtensionManagementUtility::isLoaded($options['defaultStoragePid']['extKey'])
                && array_key_exists($options['defaultStoragePid']['extKey'], $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'])
            ) {
                $extKey = $options['defaultStoragePid']['extKey'];
                $property = $options['defaultStoragePid']['property'];
                $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$extKey]);
                if (
                    MathUtility::canBeInterpretedAsInteger($extConf[$property])
                    && (int)$extConf[$property] > 0
                ) {
                    $storagePid = (int)$extConf[$property];
                }
            }

            if (empty($storagePid)) {
                $this->addMessage(
                    'You have configured a defaultStoragePid in maps2 registration, but returned value is still 0. Please check your configuration',
                    'Invalid defaultStoragePid configuration found',
                    FlashMessage::WARNING
                );
            } else {
                $defaultStoragePid = $storagePid;
            }
        }

        // High Priority: Get PID from pageTSconfig
        $tsConfig = $this->getTsConfig($foreignLocationRecord);
        if (
            array_key_exists('defaultStoragePid', $tsConfig)
            && MathUtility::canBeInterpretedAsInteger($tsConfig['defaultStoragePid'])
            && (int)$tsConfig['defaultStoragePid'] > 0
        ) {
            $defaultStoragePid = (int)$tsConfig['defaultStoragePid'];
        }


        if (empty($defaultStoragePid)) {
            $this->addMessage(
                'No PID found in pageTSconfig "ext.maps2.defaultStoragePid" and PID of current record seems to be empty, too. No POI record will be saved',
                'Can not find any valid PID to store EXT:maps2 records'
            );
        }

        return $defaultStoragePid;
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
     * @param string $uid
     * @param DataHandler $dataHandler
     * @return int
     */
    protected function getRealUid($uid, $dataHandler)
    {
        if (GeneralUtility::isFirstPartOfStr($uid, 'NEW')) {
            $uid = $dataHandler->substNEWwithIDs[$uid];
        }
        return (int)$uid;
    }

    /**
     * Get pageTSconfig for EXT:maps2
     *
     * @param array $locationRecord
     * @return array
     * @throws \Exception
     */
    public function getTsConfig(array $locationRecord): array
    {
        if (
            array_key_exists('pid', $locationRecord)
            && MathUtility::canBeInterpretedAsInteger($locationRecord['pid'])
        ) {
            $tsConfig = BackendUtility::getModTSconfig($locationRecord['pid'], 'ext.maps2');
            if (
                array_key_exists('properties', $tsConfig)
                && is_array($tsConfig['properties'])
                && !empty($tsConfig['properties'])
            ) {
                return $tsConfig['properties'];
            }
        }
        return [];
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
            $this->addMessage(
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
            $this->addMessage(
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
            $this->addMessage(
                'Error while trying to synchroniz columns of your record with maps2 record. It seems that "' . $foreignTableName . '" is not registered as table or "' . $foreignColumnName . '" is not a valid column in ' . $foreignTableName,
                'Missing table/column in TCA',
                FlashMessage::ERROR
            );
            return false;
        }

        return true;
    }

    /**
     * Add a message to FlashMessage queue
     *
     * @param string $message
     * @param string $title
     * @param int $severity
     * @return void
     */
    protected function addMessage($message, $title = '', $severity = FlashMessage::OK)
    {
        /** @var $flashMessage FlashMessage */
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            $title,
            $severity
        );
        $defaultFlashMessageQueue = $this->flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    /**
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool()
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
