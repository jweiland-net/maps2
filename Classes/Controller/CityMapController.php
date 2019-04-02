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

use JWeiland\Maps2\Domain\Model\Position;
use JWeiland\Maps2\Service\GeoCodeService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A controller class to show Google Maps for a pre-configured city
 */
class CityMapController extends AbstractController
{
    /**
     * Action show
     */
    public function showAction()
    {
    }

    /**
     * action search
     *
     * @param string $street
     * @throws \Exception
     */
    public function searchAction(string $street)
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
