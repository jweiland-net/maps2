<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Service;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Helper\MapHelper;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Tca\Maps2Registry;
use JWeiland\Maps2\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * This class contains recurring methods for both map providers.
 */
class MapService
{
    protected ConfigurationManagerInterface $configurationManager;

    protected MessageHelper $messageHelper;

    protected Maps2Registry $maps2Registry;

    protected array $settings = [];

    public function __construct(
        ConfigurationManagerInterface $configurationManager,
        MessageHelper $messageHelper,
        Maps2Registry $maps2Registry
    ) {
        $this->configurationManager = $configurationManager;
        $this->messageHelper = $messageHelper;
        $this->maps2Registry = $maps2Registry;
    }

    /**
     * Get currently valid default map provider
     *
     * @param array $databaseRow If set, we will try to retrieve map provider from this row before.
     * @return string Returns either "gm" or "osm"
     * @deprecated
     */
    public function getMapProvider(array $databaseRow = []): string
    {
        trigger_error('Method MapService::getMapProvider is deprecated and has been moved to MapHelper::getMapProvider.', E_USER_DEPRECATED);

        $mapHelper = GeneralUtility::makeInstance(MapHelper::class);

        return $mapHelper->getMapProvider($databaseRow);
    }

    /**
     * Set info window for Poi Collection
     *
     * @deprecated
     */
    public function setInfoWindow(PoiCollection $poiCollection): void
    {
        trigger_error(
            'MapService::setInfoWindow is deprecated please use MapService::renderInfoWindow directly.',
            E_USER_DEPRECATED
        );

        $poiCollection->setInfoWindowContent(
            $this->renderInfoWindow($poiCollection)
        );
    }

    /**
     * Render InfoWindow for marker
     */
    public function renderInfoWindow(PoiCollection $poiCollection): string
    {
        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->assign('settings', $this->getSettings());
        $view->assign('poiCollection', $poiCollection);
        $view->setTemplatePathAndFilename(
            GeneralUtility::getFileAbsFileName(
                $this->getInfoWindowContentTemplatePath()
            )
        );

        return $view->render();
    }

    /**
     * Get template path for info window content
     */
    protected function getInfoWindowContentTemplatePath(): string
    {
        $extConf = GeneralUtility::makeInstance(ExtConf::class);
        $settings = $this->getSettings();

        // get default template path
        if (!isset($settings['infoWindowContentTemplatePath'])) {
            return $extConf->getInfoWindowContentTemplatePath();
        }

        if (empty($settings['infoWindowContentTemplatePath'])) {
            return $extConf->getInfoWindowContentTemplatePath();
        }

        return $settings['infoWindowContentTemplatePath'];
    }

    /**
     * @return mixed[]
     */
    protected function getSettings(): array
    {
        $settings = [];
        $environmentService = GeneralUtility::makeInstance(EnvironmentService::class);
        if ($environmentService->isEnvironmentInFrontendMode()) {
            // Keep ExtName and PluginName, else the extKey will not be added to return-value
            // in further getConfiguration calls.
            $settings = $this->configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'Maps2',
                'Maps2'
            );
        }

        return $settings;
    }

    protected function getColumnRegistry(): array
    {
        return $this->maps2Registry->getColumnRegistry() ?? [];
    }

    /**
     * Creates a new poiCollection
     * Currently only 'Point' types are allowed. If you need type 'Radius' you can realize it with $overrideFieldValues.
     * If you need 'Area' or 'Route' it's up to you to implement that function within your own extension.
     *
     * @return int UID of the newly inserted record
     * @throws \Exception
     * @api
     */
    public function createNewPoiCollection(int $pid, Position $position, array $overrideFieldValues = []): int
    {
        if (empty($position->getLatitude()) || empty($position->getLongitude())) {
            $messageHelper = GeneralUtility::makeInstance(MessageHelper::class);
            $messageHelper->addFlashMessage(
                'The is no latitude or longitude in Response of Map Provider.',
                'Missing Lat or Lng',
                AbstractMessage::ERROR
            );
            return 0;
        }

        $latitude = $position->getLatitude();
        $longitude = $position->getLongitude();

        $fieldValues = [];
        $fieldValues['pid'] = $pid;
        $fieldValues['tstamp'] = time();
        $fieldValues['crdate'] = time();
        $fieldValues['cruser_id'] = $GLOBALS['BE_USER']->user['uid'] ?? 0;
        $fieldValues['hidden'] = 0;
        $fieldValues['deleted'] = 0;
        $fieldValues['latitude'] = $latitude;
        $fieldValues['longitude'] = $longitude;
        $fieldValues['collection_type'] = 'Point'; // currently only Point is allowed. If you want more: It's your turn
        $fieldValues['title'] = $position->getFormattedAddress(); // it's up to you to override this value
        $fieldValues['address'] = $position->getFormattedAddress();

        // you don't like the current fieldValues? Override them with $overrideFieldValues
        ArrayUtility::mergeRecursiveWithOverrule($fieldValues, $overrideFieldValues);

        // remove all fields, which are not set in DB
        $fieldValues = array_intersect_key(
            $fieldValues,
            DatabaseUtility::getColumnsFromTable('tx_maps2_domain_model_poicollection')
        );

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_maps2_domain_model_poicollection');
        $connection->insert(
            'tx_maps2_domain_model_poicollection',
            $fieldValues
        );

        return (int)$connection->lastInsertId('tx_maps2_domain_model_poicollection');
    }

    /**
     * Assign PoiCollection UID to foreign record
     *
     * @param int $poiCollectionUid This must be the UID of the newly created POI collection record
     * @param array $foreignRecord This is the record of the foreign extensions. It must be an already saved record and it MUST HAVE an UID assigned
     * @param string $foreignTableName This is your (foreign) location table name, from where you get the $foreignRecord
     * @param string $foreignFieldName This is our column name (mostly tx_maps2_uid) in your/foreign location table.
     * @throws \Exception
     * @api
     */
    public function assignPoiCollectionToForeignRecord(
        int $poiCollectionUid,
        array &$foreignRecord,
        string $foreignTableName,
        string $foreignFieldName = 'tx_maps2_uid'
    ): void {
        $hasErrors = false;

        if ($poiCollectionUid === 0) {
            $hasErrors = true;
            $this->messageHelper->addFlashMessage(
                'PoiCollection UID can not be empty. Please check your values near method assignPoiCollectionToForeignRecord',
                'PoiCollection empty',
                AbstractMessage::ERROR
            );
        }

        if (empty($foreignRecord)) {
            $hasErrors = true;
            $this->messageHelper->addFlashMessage(
                'Foreign record can not be empty. Please check your values near method assignPoiCollectionToForeignRecord',
                'Foreign record empty',
                AbstractMessage::ERROR
            );
        }

        if (!array_key_exists('uid', $foreignRecord)) {
            $hasErrors = true;
            $this->messageHelper->addFlashMessage(
                'Foreign record must have the array key "uid" which is currently not present. Please check your values near method assignPoiCollectionToForeignRecord',
                'UID not filled',
                AbstractMessage::ERROR
            );
        }

        if (empty(trim($foreignTableName))) {
            $hasErrors = true;
            $this->messageHelper->addFlashMessage(
                'Foreign table name is a must have value, which is currently not present. Please check your values near method assignPoiCollectionToForeignRecord',
                'Foreign table name empty',
                AbstractMessage::ERROR
            );
        }

        if (empty(trim($foreignFieldName))) {
            $hasErrors = true;
            $this->messageHelper->addFlashMessage(
                'Foreign field name is a must have value, which is currently not present. Please check your values near method assignPoiCollectionToForeignRecord',
                'Foreign field name empty',
                AbstractMessage::ERROR
            );
        }

        if ($hasErrors) {
            return;
        }

        if (!array_key_exists($foreignTableName, $GLOBALS['TCA'])) {
            $this->messageHelper->addFlashMessage(
                'Table "' . $foreignTableName . '" is not configured in TCA',
                'Table not found',
                AbstractMessage::ERROR
            );
            return;
        }

        if (!array_key_exists($foreignFieldName, $GLOBALS['TCA'][$foreignTableName]['columns'])) {
            $this->messageHelper->addFlashMessage(
                'Field "' . $foreignFieldName . '" is not configured in TCA',
                'Field not found',
                AbstractMessage::ERROR
            );
            return;
        }

        $connection = $this->getConnectionPool()->getConnectionForTable($foreignTableName);
        $connection->update(
            $foreignTableName,
            [$foreignFieldName => $poiCollectionUid],
            ['uid' => (int)$foreignRecord['uid']]
        );

        $foreignRecord[$foreignFieldName] = $poiCollectionUid;
    }

    /**
     * Adds the related foreign records of a PoiCollection to PoiCollection itself.
     */
    public function addForeignRecordsToPoiCollection(PoiCollection $poiCollection): void
    {
        $columnRegistry = $this->getColumnRegistry();
        if (empty($columnRegistry)) {
            return;
        }

        if ($poiCollection->getUid() === 0) {
            return;
        }

        // Loop through all configured tables and columns and add the foreignRecord to PoiCollection
        foreach ($columnRegistry as $tableName => $columns) {
            foreach ($columns as $columnName => $configuration) {
                $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable($tableName);
                $queryBuilder->setRestrictions(
                    GeneralUtility::makeInstance(FrontendRestrictionContainer::class)
                );
                $statement = $queryBuilder
                    ->select('*')
                    ->from($tableName)
                    ->where(
                        $queryBuilder->expr()->eq(
                            $columnName,
                            $queryBuilder->createNamedParameter($poiCollection->getUid(), \PDO::PARAM_INT)
                        )
                    )
                    ->execute();
                while ($foreignRecord = $statement->fetch()) {
                    // Hopefully these keys are unique enough
                    // Very useful to f:groupedFor in Fluid Templates
                    $foreignRecord['jwMaps2TableName'] = $tableName;
                    $foreignRecord['jwMaps2ColumnName'] = $columnName;

                    // Add or remove your own values
                    $this->emitPreAddForeignRecordToPoiCollectionSignal(
                        $foreignRecord,
                        $tableName,
                        $columnName
                    );

                    $poiCollection->addForeignRecord($foreignRecord);
                }
            }
        }
    }

    /**
     * Use this signal, if you want to modify the foreign record, before adding it to PoiCollection record.
     * If you set $foreignRecord to [] (empty) it will NOT be added to PoiCollection.
     */
    protected function emitPreAddForeignRecordToPoiCollectionSignal(
        array &$foreignRecord,
        string $tableName,
        string $columnName
    ): void {
        $signalSlotDispatcher = GeneralUtility::makeInstance(Dispatcher::class);
        $signalSlotDispatcher->dispatch(
            self::class,
            'preAddForeignRecordToPoiCollection',
            [&$foreignRecord, $tableName, $columnName]
        );
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
