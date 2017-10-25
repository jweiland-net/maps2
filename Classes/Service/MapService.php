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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Request;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\CacheService;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Class MapService
 */
class MapService
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
     * Check, if Browser is allowed to request Google Maps Servers
     *
     * @param Request $request
     *
     * @return bool
     */
    public function isGoogleMapRequestAllowed(Request $request = null)
    {
        // request parameters have highest priority
        if ($request && $request->hasArgument('explicitAllowed')) {
            return (bool)$request->getArgument('explicitAllowed');
        }

        // if not in request check configuration and session
        if ($this->extConf->getExplicitAllowGoogleMaps()) {
            return (bool)$this->getTypoScriptFrontendController()->fe_user->getKey('ses', 'allowMaps2');
        } else {
            return true;
        }
    }

    /**
     * @param Request $request
     */
    public function explicitAllowGoogleMapRequests(Request $request)
    {
        if ($this->isGoogleMapRequestAllowed($request)) {
            $this->cacheService->clearPageCache([$this->getTypoScriptFrontendController()->id]);
            $this->getTypoScriptFrontendController()->fe_user->setSessionData('allowMaps2', 1);
        }
    }

    /**
     * Show form to allow requests to google map servers
     *
     * @param Request $request
     *
     * @return string
     */
    public function showAllowMapForm(Request $request)
    {
        /** @var StandaloneView $view */
        $view = $this->objectManager->get('TYPO3\\CMS\\Fluid\\View\\StandaloneView');
        $view->setTemplatePathAndFilename(
            GeneralUtility::getFileAbsFileName(
                'EXT:maps2/Resources/Private/Templates/AllowMapForm.html'
            ));
        $view->assign('settings', $this->settings);
        $view->assign('request', $request);
        return $view->render();
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
     * @return TypoScriptFrontendController
     */
    protected function getTypoScriptFrontendController()
    {
        return $GLOBALS['TSFE'];
    }
}
