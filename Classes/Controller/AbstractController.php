<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Controller;

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
use JWeiland\Maps2\Service\GoogleMapsService;
use JWeiland\Maps2\Service\MapService;
use JWeiland\Maps2\Utility\DataMapper;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * An abstract controller to keep the other controllers small and simple
 */
class AbstractController extends ActionController
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var DataMapper
     */
    protected $dataMapper;

    /**
     * @var GoogleMapsService
     */
    protected $googleMapsService;

    /**
     * inject extConf
     *
     * @param ExtConf $extConf
     */
    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    /**
     * inject dataMapper
     *
     * @param DataMapper $dataMapper
     */
    public function injectDataMapper(DataMapper $dataMapper)
    {
        $this->dataMapper = $dataMapper;
    }

    /**
     * inject mapService
     *
     * @param GoogleMapsService $googleMapsService
     */
    public function injectGoogleMapsService(GoogleMapsService $googleMapsService)
    {
        $this->googleMapsService = $googleMapsService;
    }

    /**
     * initialize view
     * add some global vars to view
     *
     * @param ViewInterface $view The view to be initialized
     */
    public function initializeView(ViewInterface $view)
    {
        // remove unneeded columns from tt_content array
        $contentRecord = $this->configurationManager->getContentObject()->data;
        unset($contentRecord['pi_flexform'], $contentRecord['l18n_diffsource']);

        $this->prepareSettings();
        $view->assign('data', $this->configurationManager->getContentObject()->data);
        $view->assign('environment', [
            'settings' => $this->settings,
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'id' => $GLOBALS['TSFE']->id,
            'contentRecord' => $contentRecord
        ]);
    }

    /**
     * Prepare and check settings
     */
    protected function prepareSettings()
    {
        if (array_key_exists('infoWindowContentTemplatePath', $this->settings)) {
            $this->settings['infoWindowContentTemplatePath'] = trim($this->settings['infoWindowContentTemplatePath']);
        } else {
            $this->addFlashMessage('Dear Admin: Please add default static template of maps2 into your TS-Template.');
        }

        if (empty($this->settings['mapProvider'])) {
            $mapService = GeneralUtility::makeInstance(MapService::class);
            $this->controllerContext
                ->getFlashMessageQueue()
                ->enqueue($mapService->getFlashMessageForMissingStaticTemplate());
        }

        if (
            isset($this->settings['markerClusterer']['enable'])
            && !empty($this->settings['markerClusterer']['enable'])
            && isset($this->settings['markerClusterer']['imagePath'])
            && !empty($this->settings['markerClusterer']['imagePath'])
        ) {
            $this->settings['markerClusterer']['enable'] = 1;
            $this->settings['markerClusterer']['imagePath'] = PathUtility::getAbsoluteWebPath(
                GeneralUtility::getFileAbsFileName(
                    $this->settings['markerClusterer']['imagePath']
                )
            );
        }
    }
}
