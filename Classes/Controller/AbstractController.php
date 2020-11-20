<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
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

    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;

        $tsSettings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
            'maps2',
            'invalid' // invalid plugin name, to get fresh unmerged settings
        );
        $originalSettings = $this->configurationManager->getConfiguration(
            \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );

        foreach ($originalSettings as $setting => $value) {
            if (is_string($value) && $value === '') {
                $originalSettings[$setting] = $tsSettings['settings'][$setting];
            }
        }

        $this->settings = $originalSettings;
    }

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

    protected function prepareSettings()
    {
        if (array_key_exists('infoWindowContentTemplatePath', $this->settings)) {
            $this->settings['infoWindowContentTemplatePath'] = trim($this->settings['infoWindowContentTemplatePath']);
        } else {
            $this->addFlashMessage('Dear Admin: Please add default static template of maps2 into your TS-Template.');
        }

        $this->settings['forceZoom'] = (bool)$this->settings['forceZoom'] ?? false;

        if (empty($this->settings['mapProvider'])) {
            $mapService = GeneralUtility::makeInstance(MapService::class);
            $this->controllerContext
                ->getFlashMessageQueue()
                ->enqueue($mapService->getFlashMessageForMissingStaticTemplate());
        }

        // https://wiki.openstreetmap.org/wiki/Tile_servers tolds to use ${x} placeholders, but they don't work.
        if (!empty($this->settings['mapTile'])) {
            $this->settings['mapTile'] = str_replace(
                ['${s}', '${x}', '${y}', '${z}'],
                ['{s}', '{x}', '{y}', '{z}'],
                $this->settings['mapTile']
            );
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
