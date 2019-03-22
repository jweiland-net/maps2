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

use JWeiland\Maps2\Domain\Model\RadiusResult;

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
        $location = $this->googleMapsService->getFirstFoundPositionByAddress(
            strip_tags($street) . ' ' . $this->settings['autoAppend']
        );

        if ($location instanceof RadiusResult) {
            $this->view->assign('latitude', $location->getGeometry()->getLocation()->getLatitude());
            $this->view->assign('longitude', $location->getGeometry()->getLocation()->getLongitude());
            $this->view->assign('address', rawurldecode($street));
        }
    }
}
