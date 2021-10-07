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
use JWeiland\Maps2\Event\PostProcessFluidVariablesEvent;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
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

    public function __construct(ExtConf $extConf)
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

    public function initializeView(ViewInterface $view): void
    {
        $contentRecord = $this->configurationManager->getContentObject()->data;

        // Remove unneeded columns from tt_content array
        unset(
            $contentRecord['pi_flexform'],
            $contentRecord['l18n_diffsource']
        );

        $this->prepareSettings();
        $view->assign('data', $contentRecord);
        $view->assign('environment', [
            'settings' => $this->settings,
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'id' => $GLOBALS['TSFE']->id,
            'contentRecord' => $contentRecord
        ]);
    }

    protected function prepareSettings(): void
    {
        if (array_key_exists('infoWindowContentTemplatePath', $this->settings)) {
            $this->settings['infoWindowContentTemplatePath'] = trim($this->settings['infoWindowContentTemplatePath']);
        } else {
            $this->addFlashMessage('Dear Admin: Please add default static template of maps2 into your TS-Template.');
        }

        $this->settings['forceZoom'] = (bool)($this->settings['forceZoom'] ?? false);

        if (empty($this->settings['mapProvider'])) {
            $this->controllerContext
                ->getFlashMessageQueue()
                ->enqueue(GeneralUtility::makeInstance(
                    FlashMessage::class,
                    'You have forgotten to add maps2 static template for either Google Maps or OpenStreetMap',
                    'Missing static template',
                    AbstractMessage::ERROR
                ));
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
            isset(
                $this->settings['markerClusterer']['enable'],
                $this->settings['markerClusterer']['imagePath']
            )
            && !empty($this->settings['markerClusterer']['enable'])
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

    protected function postProcessAndAssignFluidVariables(array $variables = []): void
    {
        /** @var PostProcessFluidVariablesEvent $event */
        $event = $this->eventDispatcher->dispatch(
            new PostProcessFluidVariablesEvent(
                $this->request,
                $this->settings,
                $variables
            )
        );

        $this->view->assignMultiple($event->getFluidVariables());
    }

}
