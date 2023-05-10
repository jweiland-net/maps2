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
use JWeiland\Maps2\Helper\SettingsHelper;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * An abstract controller to keep the other controllers small and simple
 */
class AbstractController extends ActionController
{
    protected ExtConf $extConf;

    protected SettingsHelper $settingsHelper;

    public function injectExtConf(ExtConf $extConf): void
    {
        $this->extConf = $extConf;
    }

    public function injectSettingsHelper(SettingsHelper $settingsHelper): void
    {
        $this->settingsHelper = $settingsHelper;
    }

    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
    {
        $this->configurationManager = $configurationManager;

        $this->settings = $this->settingsHelper->getMergedSettings();
    }

    protected function initializeView($view): void
    {
        $contentRecord = $this->configurationManager->getContentObject()->data;

        // Remove unneeded columns from tt_content array
        unset(
            $contentRecord['pi_flexform'],
            $contentRecord['l18n_diffsource']
        );

        $view->assign('data', $contentRecord);
        $view->assign('environment', [
            'settings' => $this->getPreparedSettings(),
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'id' => $GLOBALS['TSFE']->id,
            'contentRecord' => $contentRecord,
        ]);
    }

    protected function getPreparedSettings(): array
    {
        if (array_key_exists('infoWindowContentTemplatePath', $this->settings)) {
            $this->settings['infoWindowContentTemplatePath'] = trim($this->settings['infoWindowContentTemplatePath']);
        } else {
            $this->addFlashMessage('Dear Admin: Please add default static template of maps2 into your TS-Template.');
        }

        if (!array_key_exists('mapProvider', $this->settings)) {
            $this->getFlashMessageQueue()
                ->enqueue(GeneralUtility::makeInstance(
                    FlashMessage::class,
                    'You have forgotten to add maps2 static template for either Google Maps or OpenStreetMap',
                    'Missing static template',
                    AbstractMessage::ERROR
                ));
        }

        return $this->settingsHelper->getPreparedSettings($this->settings);
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
