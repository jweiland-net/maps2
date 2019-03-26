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
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Service\CacheService;
use TYPO3\CMS\Extbase\Service\EnvironmentService;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * This class contains recurring methods for both map providers.
 */
class MapService
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $settings = [];

    public function __construct()
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->objectManager = $objectManager;

        $environmentService = GeneralUtility::makeInstance(EnvironmentService::class);
        if ($environmentService->isEnvironmentInFrontendMode()) {
            $configurationManager = $objectManager->get(ConfigurationManagerInterface::class);

            $this->settings = $configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
            );
        }
    }

    /**
     * Show form to allow requests to Google Maps2 servers
     */
    public function showAllowMapForm(): string
    {
        if (
            is_array($this->settings)
            && (
                !array_key_exists('mapProvider', $this->settings)
                || empty($this->settings['mapProvider'])
            )
        ) {
            $flashMessage = $this->getFlashMessageForMissingStaticTemplate();
            $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
            $flashMessageQueue = $flashMessageService->getMessageQueueByIdentifier('maps2.allowMap');
            $flashMessageQueue->enqueue($flashMessage);
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
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
     * Returns a FlashMessage with a hint on a missing static template
     *
     * @return FlashMessage
     */
    public function getFlashMessageForMissingStaticTemplate(): FlashMessage
    {
        return GeneralUtility::makeInstance(
            FlashMessage::class,
            'You have forgotten to add maps2 static template for either Google Maps or OpenStreetMap',
            'Missing static template',
            AbstractMessage::ERROR
        );
    }

    /**
     * Get request URI
     */
    protected function getRequestUri(): string
    {
        /** @var UriBuilder $uriBuilder */
        $uriBuilder = $this->objectManager->get(UriBuilder::class);

        return $uriBuilder->reset()
            ->setAddQueryString(true)
            ->setArguments([
                'tx_maps2_maps2' => [
                    'mapProviderRequestsAllowedForMaps2' => 1
                ]
            ])
            ->setArgumentsToBeExcludedFromQueryString(['cHash'])
            ->build();
    }

    /**
     * Get template path for info window content
     */
    protected function getAllowMapTemplatePath(): string
    {
        $extConf = GeneralUtility::makeInstance(ExtConf::class);

        // get default template path
        $path = $extConf->getAllowMapTemplatePath();
        if (
            isset($this->settings['allowMapTemplatePath'])
            && !empty($this->settings['allowMapTemplatePath'])
        ) {
            $path = $this->settings['allowMapTemplatePath'];
        }

        return $path;
    }

    /**
     * Get currently valid default map provider
     *
     * @param array $databaseRow If set, we will try to retrieve map provider from this row before.
     * @return string
     */
    public function getMapProvider(array $databaseRow = []): string
    {
        $mapProvider = '';
        $extConf = GeneralUtility::makeInstance(ExtConf::class);

        // Only if both map providers are allowed, we can read map provider from Database
        if ($extConf->getMapProvider() === 'both') {
            if (!empty($databaseRow)) {
                $mapProvider = $this->getMapProviderFromDatabase($databaseRow);
            }

            if (empty($mapProvider)) {
                $mapProvider = $extConf->getDefaultMapProvider();
            }
        } else {
            // We have a strict map provider.
            $mapProvider = $extConf->getMapProvider();
        }

        return $mapProvider;
    }

    /**
     * Try to retrieve a default map provider from given database record
     *
     * @param array $databaseRow
     * @return string
     */
    public function getMapProviderFromDatabase(array $databaseRow): string
    {
        $mapProvider = '';

        if (array_key_exists('map_provider', $databaseRow)) {
            if (is_array($databaseRow['map_provider'])) {
                // We have a record from TCEMAIN
                $mapProvider = current($databaseRow['map_provider']);
            } elseif (is_string($databaseRow['map_provider']) && !empty($databaseRow['map_provider'])) {
                // We have a normal array based record from database
                $mapProvider = $databaseRow['map_provider'];
            }
        }

        return $mapProvider;
    }
}
