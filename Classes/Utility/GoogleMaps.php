<?php
namespace JWeiland\Maps2\Utility;

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
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class GoogleMaps {

	/**
	 * @var string
	 */
	protected $urlGeocode = 'http://maps.googleapis.com/maps/api/geocode/json?address=|&sensor=false';

	/**
	 * @var \JWeiland\Maps2\Utility\DataMapper
	 */
	protected $dataMapper;

	/**
	 * inject dataMapper
	 *
	 * @param \JWeiland\Maps2\Utility\DataMapper $dataMapper
	 * @return void
	 */
	public function injectDataMapper(\JWeiland\Maps2\Utility\DataMapper $dataMapper) {
		$this->dataMapper = $dataMapper;
	}

	/**
	 * find position by address
	 *
	 * @param string $address
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function findPositionByAddress($address) {
		$url = str_replace('|', $this->updateAddressForUri($address), $this->urlGeocode);
		$json = GeneralUtility::getUrl($url);
		$response = json_decode($json, TRUE);
		return $this->dataMapper->mapObjectStorage('JWeiland\\Maps2\\Domain\\Model\\RadiusResult', $response['results']);
	}

	/**
	 * prepare address for an uri
	 * further it will add some additional information like country
	 *
	 * @param string $address The address to update
	 * @return string A prepared address which is valid for an uri
	 */
	protected function updateAddressForUri($address) {
		// check if it can be interpreted as a zip code
		if(MathUtility::canBeInterpretedAsInteger($address) && strlen($address) == 5) {
			$address .= ' Deutschland';
		}
		return rawurlencode($address);
	}

}