<?php
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

/**
 * A controller class to show Google Maps for a pre-configured city
 */
class CityMapController extends AbstractController
{
    /**
     * Action show
     *
     * @return void
     */
    public function showAction()
    {
    }

    /**
     * action search
     *
     * @param string $street
     *
     * @return void
     */
    public function searchAction($street)
    {
        $response = $this->geocodeUtility->findPositionByAddress(
            strip_tags($street) . ' ' . $this->settings['autoAppend']
        );

        /* @var \JWeiland\Maps2\Domain\Model\RadiusResult $location */
        $location = $response->current();
        $this->view->assign('latitude', $location->getGeometry()->getLocation()->getLatitude());
        $this->view->assign('longitude', $location->getGeometry()->getLocation()->getLongitude());
        $this->view->assign('address', rawurldecode($street));
    }
}
