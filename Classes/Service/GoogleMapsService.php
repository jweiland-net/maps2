<?php
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
use JWeiland\Maps2\Utility\DatabaseUtility;
use JWeiland\Maps2\Utility\DataMapper;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
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
 * Class GoogleMapsService
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
     * @var FlashMessageService
     */
    protected $flashMessageService;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * inject extConf
     *
     * @param ExtConf $extConf
     *
     * @return void
     */
    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * inject cacheService
     *
     * @param CacheService $cacheService
     *
     * @return void
     */
    public function injectCacheService(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * inject flashMessageService
     *
     * @param FlashMessageService $flashMessageService
     * @return void
     */
    public function injectFlashMessageService(FlashMessageService $flashMessageService)
    {
        $this->flashMessageService = $flashMessageService;
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
     * inject objectManager
     *
     * @param ObjectManager $objectManager
     *
     * @return void
     */
    public function injectObjectManager(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Show form to allow requests to Google Maps2 servers
     *
     * @return string
     */
    public function showAllowMapForm()
    {
        /** @var StandaloneView $view */
        $view = $this->objectManager->get(StandaloneView::class);
        $view->setTemplatePathAndFilename(
            GeneralUtility::getFileAbsFileName(
                $this->getAllowMapTemplatePath()
            )
        );
        $view->assign('settings', $this->settings);
        $view->assign('requestUri', $this->getRequestUri());

        return $view->render();
    }

    /**
     * Get request URI
     *
     * @return string
     */
    protected function getRequestUri()
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);

        return $uriBuilder->reset()
            ->setAddQueryString(true)
            ->setArguments([
                'tx_maps2_maps2' => [
                    'googleRequestsAllowedForMaps2' => 1
                ]
            ])
            ->setArgumentsToBeExcludedFromQueryString(['cHash'])
            ->build();
    }

    /**
     * Set info window for Poi Collection
     *
     * @param PoiCollection $poiCollection
     *
     * @return void
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
     *
     * @return string
     */
    protected function renderInfoWindow(PoiCollection $poiCollection)
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
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
     *
     * @return string
     */
    protected function getInfoWindowContentTemplatePath()
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
     * Get template path for info window content
     *
     * @return string
     */
    protected function getAllowMapTemplatePath()
    {
        // get default template path
        $path = $this->extConf->getAllowMapTemplatePath();
        if (
            isset($this->settings['allowMapTemplatePath']) &&
            !empty($this->settings['allowMapTemplatePath'])
        ) {
            $path = $this->settings['allowMapTemplatePath'];
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
    public function getPositionsByAddress($address)
    {
        /** @var ObjectStorage $positions */
        $positions = $this->objectManager->get(ObjectStorage::class);

        /** @var GoogleMapsClient $client */
        $client = $this->objectManager->get(GoogleMapsClient::class);

        /** @var GeocodeRequest $geocodeRequest */
        $geocodeRequest = $this->objectManager->get(GeocodeRequest::class);
        $geocodeRequest->setAddress((string)$address);

        $response = $client->processRequest($geocodeRequest);
        if (!empty($response)) {
            /** @var DataMapper $dataMapper */
            $dataMapper = $this->objectManager->get(DataMapper::class);
            $positions = $dataMapper->mapObjectStorage(RadiusResult::class, $response['results']);
        }

        return $positions;
    }

    /**
     * Get first found position by address
     *
     * @param string $address
     * @return RadiusResult
     * @throws \Exception
     * @api
     */
    public function getFirstFoundPositionByAddress($address)
    {
        $position = null;
        $positions = $this->getPositionsByAddress((string)$address);
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
    public function createNewPoiCollection($pid, RadiusResult $position, array $overrideFieldValues = [])
    {
        $geometry = $position->getGeometry();
        if (
            $geometry instanceof RadiusResult\Geometry
            && $geometry->getLocation() instanceof Location
        ) {
            $latitude = $geometry->getLocation()->getLat();
            $longitude = $geometry->getLocation()->getLng();
        } else {
            $this->addMessage(
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
     * @param int $poiCollectionUid
     * @param array $foreignRecord This array MUST HAVE an UID assigned
     * @param string $foreignTableName
     * @param string $foreignFieldName
     * @return void
     * @throws \Exception
     * @api
     */
    public function assignPoiCollectionToForeignRecord($poiCollectionUid, array &$foreignRecord, $foreignTableName, $foreignFieldName = 'tx_maps2_uid')
    {
        $hasErrors = false;

        if (empty(trim($poiCollectionUid))) {
            $hasErrors = true;
            $this->addMessage(
                'PoiCollection UID can not be empty. Please check your values near method assignPoiCollectionToForeignRecord',
                'PoiCollection empty',
                FlashMessage::ERROR
            );
        }

        if (empty($foreignRecord)) {
            $hasErrors = true;
            $this->addMessage(
                'Foreign record can not be empty. Please check your values near method assignPoiCollectionToForeignRecord',
                'Foreign record empty',
                FlashMessage::ERROR
            );
        }

        if (!array_key_exists('uid', $foreignRecord)) {
            $hasErrors = true;
            $this->addMessage(
                'Foreign record must have the array key "uid" which is currently not present. Please check your values near method assignPoiCollectionToForeignRecord',
                'UID not filled',
                FlashMessage::ERROR
            );
        }

        if (empty(trim($foreignTableName))) {
            $hasErrors = true;
            $this->addMessage(
                'Foreign table name is a must have value, which is currently not present. Please check your values near method assignPoiCollectionToForeignRecord',
                'Foreign table name empty',
                FlashMessage::ERROR
            );
        }

        if (empty(trim($foreignFieldName))) {
            $hasErrors = true;
            $this->addMessage(
                'Foreign field name is a must have value, which is currently not present. Please check your values near method assignPoiCollectionToForeignRecord',
                'Foreign field name empty',
                FlashMessage::ERROR
            );
        }

        if ($hasErrors) {
            return;
        }

        if (!array_key_exists($foreignTableName, $GLOBALS['TCA'])) {
            $this->addMessage(
                'Table "' . $foreignTableName . '" is not configured in TCA',
                'Table not found',
                FlashMessage::ERROR
            );
            return;
        }

        if (!array_key_exists($foreignFieldName, $GLOBALS['TCA'][$foreignTableName]['columns'])) {
            $this->addMessage(
                'Field "' . $foreignFieldName . '" is not configured in TCA',
                'Field not found',
                FlashMessage::ERROR
            );
            return;
        }

        $connection = $this->getConnectionPool()->getConnectionForTable($foreignTableName);
        $connection->update(
            $foreignTableName,
            [
                $foreignFieldName => (int)$poiCollectionUid
            ],
            [
                'uid' => (int)$foreignRecord['uid']
            ]
        );
        $foreignRecord[$foreignFieldName] = (int)$poiCollectionUid;
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
