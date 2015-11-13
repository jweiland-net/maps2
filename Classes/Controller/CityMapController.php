<?php
namespace JWeiland\Maps2\Controller;

/**
 * This file is part of the TYPO3 CMS project.
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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class CityMapController
 *
 * @category Controller
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class CityMapController extends AbstractController
{

    /**
     * action show
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
     * @return void
     */
    public function searchAction($street)
    {
        $address = rawurlencode(rtrim(strip_tags($street) . ' ' . $this->settings['autoAppend']));
        $jSon = GeneralUtility::getUrl(
            'http://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&sensor=false'
        );
        $response = json_decode($jSon, true);
        $locations = $this->dataMapper->mapObjectStorage(
            'JWeiland\\Maps2\\Domain\\Model\\RadiusResult',
            $response['results']
        );

        /* @var $location \JWeiland\Maps2\Domain\Model\RadiusResult */
        $location = $locations->current();
        $this->view->assign('latitude', $location->getGeometry()->getLocation()->getLatitude());
        $this->view->assign('longitude', $location->getGeometry()->getLocation()->getLongitude());
        $this->view->assign('address', rawurldecode($street));
    }
}
