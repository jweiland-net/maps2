<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Controller;

use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A controller class to show Maps for a pre-configured city
 */
class CityMapController extends AbstractController
{
    public function showAction(): void {}

    public function searchAction(string $street): void
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
    }
}
