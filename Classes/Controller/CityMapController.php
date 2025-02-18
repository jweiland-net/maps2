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
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * A controller class to show Maps for a pre-configured city
 */
class CityMapController extends ActionController
{
    use InjectExtConfTrait;
    use InjectSettingsHelperTrait;

    protected GeoCodeService $geoCodeService;

    public function injectGeoCodeService(GeoCodeService $geoCodeService): void
    {
        $this->geoCodeService = $geoCodeService;
    }

    public function initializeObject(): void
    {
        $this->settings = $this->settingsHelper->getMergedSettings();
    }

    protected function initializeView($view): void
    {
        $contentRecord = $this->request->getAttribute('currentContentObject')->data;

        // Remove unneeded columns from tt_content array
        unset(
            $contentRecord['pi_flexform'],
            $contentRecord['l18n_diffsource'],
        );

        $view->assign('data', $contentRecord);
        $view->assign('environment', [
            'settings' => $this->settingsHelper->getPreparedSettings($this->settings),
            'extConf' => ObjectAccess::getGettableProperties($this->extConf),
            'id' => $this->getPageArguments()->getPageId(),
            'contentRecord' => $contentRecord,
        ]);
    }

    public function showAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function searchAction(string $street): ResponseInterface
    {
        $position = $this->geoCodeService->getFirstFoundPositionByAddress(
            strip_tags($street) . ' ' . $this->settings['autoAppend'],
        );

        if ($position instanceof Position) {
            $this->view->assign('latitude', $position->getLatitude());
            $this->view->assign('longitude', $position->getLongitude());
            $this->view->assign('address', rawurldecode($street));
        }

        return $this->htmlResponse();
    }

    protected function getPageArguments(): PageArguments
    {
        return $this->request->getAttribute('routing');
    }
}
