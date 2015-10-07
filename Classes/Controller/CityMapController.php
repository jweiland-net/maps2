<?php
namespace JWeiland\Maps2\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Stefan Froemken <projects@jweiland.net>, jweiland.net
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CityMapController extends \JWeiland\Maps2\Controller\AbstractController {

	/**
	 * initialize view
	 * add some global vars to view
	 *
	 * @return void
	 */
	public function initializeView() {
		$this->view->assign('extConf', ObjectAccess::getGettableProperties($this->extConf));
		$this->view->assign('id', $GLOBALS['TSFE']->id);
	}

	/**
	 * action show
	 *
	 * @return void
	 */
	public function showAction() {}

	/**
	 * action search
	 *
	 * @param string $street
	 * @return void
	 */
	public function searchAction($street) {
		$address = rawurlencode(rtrim(strip_tags($street) . ' ' . $this->settings['autoAppend']));
		$jSon = GeneralUtility::getUrl('http://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&sensor=false');
		$response = json_decode($jSon, TRUE);
		$locations = $this->dataMapper->mapObjectStorage('JWeiland\\Maps2\\Domain\\Model\\RadiusResult', $response['results']);

		/* @var $location \JWeiland\Maps2\Domain\Model\RadiusResult */
		$location = $locations->current();
		$this->view->assign('latitude', $location->getGeometry()->getLocation()->getLatitude());
		$this->view->assign('longitude', $location->getGeometry()->getLocation()->getLongitude());
		$this->view->assign('address', rawurldecode($street));
	}

}