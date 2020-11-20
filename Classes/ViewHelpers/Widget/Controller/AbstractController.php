<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\ViewHelpers\Widget\Controller;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Service\MapService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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

    public function initializeView(ViewInterface $view)
    {
        if (array_key_exists('infoWindowContentTemplatePath', $this->settings)) {
            $this->settings['infoWindowContentTemplatePath'] = trim($this->settings['infoWindowContentTemplatePath']);
        } else {
            $this->addFlashMessage('Dear Admin: Please add default static template of maps2 into your TS-Template.');
        }

        ArrayUtility::mergeRecursiveWithOverrule($this->defaultSettings, $this->getMaps2TypoScriptSettings());

        $this->prepareSettings();
        $view->assign('data', $this->configurationManager->getContentObject()->data);
        $view->assign('environment', [
            'settings' => $this->defaultSettings,
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'id' => $GLOBALS['TSFE']->id,
            'contentRecord' => $this->configurationManager->getContentObject()->data
        ]);
    }

    protected function prepareSettings()
    {
        if (array_key_exists('infoWindowContentTemplatePath', $this->defaultSettings)) {
            $this->defaultSettings['infoWindowContentTemplatePath'] = trim($this->defaultSettings['infoWindowContentTemplatePath']);
        } else {
            $this->addFlashMessage('Dear Admin: Please add default static template of maps2 into your TS-Template.');
        }

        $this->settings['forceZoom'] = (bool)$this->settings['forceZoom'] ?? false;

        // https://wiki.openstreetmap.org/wiki/Tile_servers tolds to use ${x} placeholders, but they don't work.
        if (!empty($this->defaultSettings['mapTile'])) {
            $this->defaultSettings['mapTile'] = str_replace(
                ['${s}', '${x}', '${y}', '${z}'],
                ['{s}', '{x}', '{y}', '{z}'],
                $this->defaultSettings['mapTile']
            );
        }
    }

    protected function getMaps2TypoScriptSettings(): array
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
