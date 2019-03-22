<?php
declare(strict_types=1);
namespace JWeiland\Maps2\Service;

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

use JWeiland\Maps2\Client\GoogleMapsClient;
use JWeiland\Maps2\Client\Request\GeocodeRequest;
use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\Location;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\RadiusResult;
use JWeiland\Maps2\Helper\MessageHelper;
use JWeiland\Maps2\Utility\DatabaseUtility;
use JWeiland\Maps2\Utility\DataMapper;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Service\CacheService;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * With this class you can start requests to Google Maps GeoCode API. Search for addresses, assign a POI
 * to a foreign record, save the foreign record and many more. It is designed as an API.
 */
class GoogleMapsService implements SingletonInterface
{
    /**
     * Contains the settings of the current extension
     *
     * @var array
     */
    protected $settings = [];

    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var CacheService
     */
    protected $cacheService;

    /**
     * @var MessageHelper
     */
    protected $messageHelper;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * Inject extConf
     *
     * @param ExtConf $extConf
     */
    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * Inject cacheService
     *
     * @param CacheService $cacheService
     */
    public function injectCacheService(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Inject MessageHelper
     *
     * @param MessageHelper $messageHelper
     */
    public function injectMessageHelper(MessageHelper $messageHelper)
    {
        $this->messageHelper = $messageHelper;
    }

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS);
    }

    /**
     * Inject objectManager
     *
     * @param ObjectManager $objectManager
     */
    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Set info window for Poi Collection
     *
     * @param PoiCollection $poiCollection
     */
    public function setInfoWindow(PoiCollection $poiCollection)
    {
        $poiCollection->setInfoWindowContent(
            $this->renderInfoWindow($poiCollection)
        );
    }

    /**
     * Render InfoWindow for marker
     *
     * @param PoiCollection $poiCollection
     * @return string
     */
    protected function renderInfoWindow(PoiCollection $poiCollection): string
    {
        $view = $this->objectManager->get(StandaloneView::class);
        $view->assign('settings', $this->settings);
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
        // get default template path
        $path = $this->extConf->getInfoWindowContentTemplatePath();
        if (
            isset($this->settings['infoWindowContentTemplatePath']) &&
            !empty($this->settings['infoWindowContentTemplatePath'])
        ) {
            $path = $this->settings['infoWindowContentTemplatePath'];
        }

        return $path;
    }

    /**
     * Get positions by address
     *
     * @param string $address
     * @return ObjectStorage|RadiusResult[]
     * @throws \Exception
     * @api
     */
    public function getPositionsByAddress(string $address): ObjectStorage
    {
        $positions = $this->objectManager->get(ObjectStorage::class);

        // Prevent calls to Google GeoCode API, if address is empty
        if (empty(trim($address))) {
            return $positions;
        }

        $geocodeRequest = $this->objectManager->get(GeocodeRequest::class);
        $geocodeRequest->setAddress((string)$address);

        $client = $this->objectManager->get(GoogleMapsClient::class);
        $response = $client->processRequest($geocodeRequest);
        if (!empty($response)) {
            $dataMapper = $this->objectManager->get(DataMapper::class);
            $positions = $dataMapper->mapObjectStorage(RadiusResult::class, $response['results']);
        }

        return $positions;
    }

    /**
     * Get first found position by address
     *
     * @param string $address
     * @return RadiusResult|null
     * @throws \Exception
     * @api
     */
    public function getFirstFoundPositionByAddress(string $address)
    {
        $position = null;
        $positions = $this->getPositionsByAddress($address);
        if ($positions->count()) {
            $positions->rewind();
            $position = $positions->current();
        }

        return $position;
    }

    /**
     * Creates a new poiCollection
     * Currently only 'Point' types are allowed. If you need type 'Radius' you can realize it with $overrideFieldValues.
     * If you need 'Area' or 'Route' it's up to you to implement that function within your own extension.
     *
     * @param int $pid
     * @param RadiusResult $position
     * @param array $overrideFieldValues
     * @return int UID of the newly inserted record
     * @throws \Exception
     * @api
     */
    public function createNewPoiCollection($pid, RadiusResult $position, array $overrideFieldValues = []): int
    {
        $geometry = $position->getGeometry();
        if (
            $geometry instanceof RadiusResult\Geometry
            && $geometry->getLocation() instanceof Location
        ) {
            $latitude = $geometry->getLocation()->getLat();
            $longitude = $geometry->getLocation()->getLng();
        } else {
            $this->messageHelper->addFlashMessage(
                'The domain model RadiusResult seems to be broken after processing in DataMapper. Can not find Latitude and Longitude.',
                'RadiusResult broken',
                FlashMessage::ERROR
            );
            return 0;
        }

        $fieldValues = [];
        $fieldValues['pid'] = (int)$pid;
        $fieldValues['tstamp'] = time();
        $fieldValues['crdate'] = time();
        $fieldValues['cruser_id'] = $GLOBALS['BE_USER']->user['uid'];
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
    public function assignPoiCollectionToForeignRecord(int $poiCollectionUid, array &$foreignRecord, string $foreignTableName, string $foreignFieldName = 'tx_maps2_uid')
    {
        $hasErrors = false;

        if ($poiCollectionUid === 0) {
            $hasErrors = true;
            $this->messageHelper->addFlashMessage(
                'PoiCollection UID can not be empty. Please check your values near method assignPoiCollectionToForeignRecord',
                'PoiCollection empty',
                FlashMessage::ERROR
            );
        }

        if (empty($foreignRecord)) {
            $hasErrors = true;
            $this->messageHelper->addFlashMessage(
                'Foreign record can not be empty. Please check your values near method assignPoiCollectionToForeignRecord',
                'Foreign record empty',
                FlashMessage::ERROR
            );
        }

        if (!array_key_exists('uid', $foreignRecord)) {
            $hasErrors = true;
            $this->messageHelper->addFlashMessage(
                'Foreign record must have the array key "uid" which is currently not present. Please check your values near method assignPoiCollectionToForeignRecord',
                'UID not filled',
                FlashMessage::ERROR
            );
        }

        if (empty(trim($foreignTableName))) {
            $hasErrors = true;
            $this->messageHelper->addFlashMessage(
                'Foreign table name is a must have value, which is currently not present. Please check your values near method assignPoiCollectionToForeignRecord',
                'Foreign table name empty',
                FlashMessage::ERROR
            );
        }

        if (empty(trim($foreignFieldName))) {
            $hasErrors = true;
            $this->messageHelper->addFlashMessage(
                'Foreign field name is a must have value, which is currently not present. Please check your values near method assignPoiCollectionToForeignRecord',
                'Foreign field name empty',
                FlashMessage::ERROR
            );
        }

        if ($hasErrors) {
            return;
        }

        if (!array_key_exists($foreignTableName, $GLOBALS['TCA'])) {
            $this->messageHelper->addFlashMessage(
                'Table "' . $foreignTableName . '" is not configured in TCA',
                'Table not found',
                FlashMessage::ERROR
            );
            return;
        }

        if (!array_key_exists($foreignFieldName, $GLOBALS['TCA'][$foreignTableName]['columns'])) {
            $this->messageHelper->addFlashMessage(
                'Field "' . $foreignFieldName . '" is not configured in TCA',
                'Field not found',
                FlashMessage::ERROR
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
     * Get TYPO3s Connection Pool
     *
     * @return ConnectionPool
     */
    protected function getConnectionPool()
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
