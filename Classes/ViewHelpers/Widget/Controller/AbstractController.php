<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\ViewHelpers\Widget\Controller;

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
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;
use TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetController;

/**
 * An abstract controller to keep the other widget controllers small and simple
 */
abstract class AbstractController extends AbstractWidgetController
{
    /**
     * @var ExtConf
     */
    protected $extConf;

    /**
     * @var MapService
     */
    protected $mapService;

    /**
     * @var array
     */
    protected $defaultSettings = [];

    public function injectExtConf(ExtConf $extConf)
    {
        $this->extConf = $extConf;
    }

    public function injectMapService(MapService $mapService)
    {
        $this->mapService = $mapService;
    }

    /**
     * initialize view
     * add some global vars to view
     *
     * @param ViewInterface $view
     */
    public function initializeView(ViewInterface $view)
    {
        ArrayUtility::mergeRecursiveWithOverrule($this->defaultSettings, $this->getMaps2TypoScriptSettings());

        if (array_key_exists('infoWindowContentTemplatePath', $this->defaultSettings)) {
            $this->defaultSettings['infoWindowContentTemplatePath'] = trim($this->defaultSettings['infoWindowContentTemplatePath']);
        } else {
            $this->addFlashMessage('Dear Admin: Please add default static template of maps2 into your TS-Template.');
        }

        $this->prepareSettings();
        $view->assign('data', $this->configurationManager->getContentObject()->data);
        $view->assign('environment', [
            'settings' => $this->defaultSettings,
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'id' => $GLOBALS['TSFE']->id,
            'contentRecord' => $this->configurationManager->getContentObject()->data
        ]);
    }

    /**
     * Prepare and check settings
     */
    protected function prepareSettings()
    {
        if (array_key_exists('infoWindowContentTemplatePath', $this->defaultSettings)) {
            $this->defaultSettings['infoWindowContentTemplatePath'] = trim($this->defaultSettings['infoWindowContentTemplatePath']);
        } else {
            $this->addFlashMessage('Dear Admin: Please add default static template of maps2 into your TS-Template.');
        }

        $this->defaultSettings['forceZoom'] = (bool)$this->defaultSettings['forceZoom'] ?? false;

        // https://wiki.openstreetmap.org/wiki/Tile_servers tolds to use ${x} placeholders, but they don't work.
        if (!empty($this->defaultSettings['mapTile'])) {
            $this->defaultSettings['mapTile'] = str_replace(
                ['${s}', '${x}', '${y}', '${z}'],
                ['{s}', '{x}', '{y}', '{z}'],
                $this->defaultSettings['mapTile']
            );
        }

        if (
            isset($this->defaultSettings['markerClusterer']['enable'])
            && !empty($this->defaultSettings['markerClusterer']['enable'])
            && isset($this->defaultSettings['markerClusterer']['imagePath'])
            && !empty($this->defaultSettings['markerClusterer']['imagePath'])
        ) {
            $this->defaultSettings['markerClusterer']['enable'] = 1;
            $this->defaultSettings['markerClusterer']['imagePath'] = PathUtility::getAbsoluteWebPath(
                GeneralUtility::getFileAbsFileName(
                    $this->defaultSettings['markerClusterer']['imagePath']
                )
            );
        }
    }

    /**
     * Get TypoScript settings of maps2
     *
     * @return array
     */
    protected function getMaps2TypoScriptSettings()
    {
        $fullTypoScript = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT
        );
        if (ArrayUtility::isValidPath($fullTypoScript, 'plugin./tx_maps2./settings.')) {
            $typoScriptService = GeneralUtility::makeInstance(TypoScriptService::class);
            $settings = ArrayUtility::getValueByPath($fullTypoScript, 'plugin./tx_maps2./settings.');
            return $typoScriptService->convertTypoScriptArrayToPlainArray($settings);
        } else {
            return [];
        }
    }
}
