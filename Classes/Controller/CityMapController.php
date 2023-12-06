<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller;

use JWeiland\Maps2\Controller\Traits\InjectExtConfTrait;
use JWeiland\Maps2\Controller\Traits\InjectSettingsHelperTrait;
use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * A controller class to show Maps for a pre-configured city
 */
class CityMapController extends ActionController
{
    use InjectExtConfTrait;
    use InjectSettingsHelperTrait;

    public function initializeObject(): void
    {
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
            'id' => (int)$this->request->getQueryParams()['id'] ?? 0,
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
                    ContextualFeedbackSeverity::ERROR
                ));
        }

        return $this->settingsHelper->getPreparedSettings($this->settings);
    }

    public function showAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function searchAction(string $street): ResponseInterface
    {
        $geoCodeService = GeneralUtility::makeInstance(GeoCodeService::class);
        $position = $geoCodeService->getFirstFoundPositionByAddress(
            strip_tags($street) . ' ' . $this->settings['autoAppend']
        );

        if ($position instanceof Position) {
            $this->view->assign('latitude', $position->getLatitude());
            $this->view->assign('longitude', $position->getLongitude());
            $this->view->assign('address', rawurldecode($street));
        }

        return $this->htmlResponse();
    }
}
