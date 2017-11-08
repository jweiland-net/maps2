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

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Domain\Model\PoiCollection;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\CacheService;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class MapService
 */
class MapService implements SingletonInterface
{
    /**
     * Contains the settings of the current extension
     *
     * @var array
     */
    protected $settings;

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
     * Initialize Session var, if configured
     *
     * @return void
     */
    public function initializeObject()
    {
        if (
            $this->extConf->getExplicitAllowGoogleMapsBySessionOnly()
            && (!isset($_SESSION) || !is_array($_SESSION))
        ) {
            session_start();
        }
    }

    /**
     * Check, if Browser(Cookie) is allowed to request Google Maps Servers
     *
     * @return bool
     */
    public function isGoogleMapRequestAllowed()
    {
        if ($this->extConf->getExplicitAllowGoogleMaps()) {
            if ($this->extConf->getExplicitAllowGoogleMapsBySessionOnly()) {
                return (bool)$_SESSION['googleRequestsAllowedForMaps2'];
            } else {
                if ($this->getTypoScriptFrontendController() instanceof TypoScriptFrontendController) {
                    return (bool)$this->getTypoScriptFrontendController()->fe_user->getSessionData('googleRequestsAllowedForMaps2');
                } else {
                    return false;
                }
            }
        } else {
            return true;
        }
    }

    /**
     * Explicit allow google map requests and store to session
     *
     * @return void
     */
    public function explicitAllowGoogleMapRequests()
    {
        $parameters = GeneralUtility::_GPmerged('tx_maps2_maps2');
        if (
            isset($parameters['googleRequestsAllowedForMaps2'])
            && (int)$parameters['googleRequestsAllowedForMaps2'] === 1
            && $this->extConf->getExplicitAllowGoogleMaps()
        ) {
            // $this->cacheService->clearPageCache([$this->getTypoScriptFrontendController()->id]);

            if (
                $this->extConf->getExplicitAllowGoogleMapsBySessionOnly()
                && empty($_SESSION['googleRequestsAllowedForMaps2'])
            ) {
                $_SESSION['googleRequestsAllowedForMaps2'] = 1;
            }

            if (
                !$this->extConf->getExplicitAllowGoogleMapsBySessionOnly()
                && (bool)$this->getTypoScriptFrontendController()->fe_user->getSessionData('googleRequestsAllowedForMaps2') === false
            ) {
                $this->getTypoScriptFrontendController()->fe_user->setAndSaveSessionData('googleRequestsAllowedForMaps2', 1);
            }
        }
    }

    /**
     * Show form to allow requests to google map servers
     *
     * @return string
     */
    public function showAllowMapForm()
    {
        /** @var StandaloneView $view */
        $view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(
            GeneralUtility::getFileAbsFileName(
                $this->getAllowMapTemplatePath()
            ));
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
        $uriBuilder = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder');

        return $uriBuilder->reset()
            ->setAddQueryString(true)
            ->setArguments(array(
                'tx_maps2_maps2' => array(
                    'googleRequestsAllowedForMaps2' => 1
                )
            ))
            ->setArgumentsToBeExcludedFromQueryString(['cHash'])
            ->build();
    }

    /**
     * Set info window for poi colleciton
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
     *
     * @return string
     */
    protected function renderInfoWindow(PoiCollection $poiCollection)
    {
        /** @var \TYPO3\CMS\Fluid\View\StandaloneView $view */
        $view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
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
     * @return TypoScriptFrontendController|null
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
