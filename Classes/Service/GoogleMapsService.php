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
use JWeiland\Maps2\Domain\Model\PoiCollection;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Domain\Model\RadiusResult;
use JWeiland\Maps2\Utility\DataMapper;
use TYPO3\CMS\Core\SingletonInterface;
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
     * Find position by address
     *
     * @param string $address
     * @return ObjectStorage|Position[]
     * @throws \Exception
     */
    public function findPositionsByAddress($address)
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
}
