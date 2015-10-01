<?php
namespace JWeiland\Maps2\Ajax;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Stefan Froemken <sfroemken@jweiland.net>, jweiland.net
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
use JWeiland\Maps2\Domain\Model\PoiCollection;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ModifyMarker extends AbstractAjaxRequest {

	/**
	 * @var \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository
	 */
	protected $poiCollectionRepository;

	/**
	 * inject poiCollectionRepository
	 *
	 * @param \JWeiland\Maps2\Domain\Repository\PoiCollectionRepository $poiCollectionRepository
	 * @return void
	 */
	public function injectPoiCollectionRepository(\JWeiland\Maps2\Domain\Repository\PoiCollectionRepository $poiCollectionRepository) {
		$this->poiCollectionRepository = $poiCollectionRepository;
	}

	/**
	 * process ajax request
	 *
	 * @param array $arguments Arguments to process
	 * @param string $hash A generated hash value to verify that there are no modifications in the uri
	 * @return string
	 */
	public function processAjaxRequest(array $arguments, $hash) {
		// cast arguments
		$uid = (int)$arguments['uid'];
		$lat = (float)$arguments['coords']['latitude'];
		$lng = (float)$arguments['coords']['longitude'];

		$poiCollection = $this->poiCollectionRepository->findByUid($uid);

		if ($poiCollection instanceof PoiCollection) {
			// validate uri arguments
			if (!$this->validateArguments($poiCollection, $hash)) {
				return 'arguments are not valid';
			}

			$poiCollection = $this->updateMarker($poiCollection, $lat, $lng);
			$this->poiCollectionRepository->update($poiCollection);
			$this->persistenceManager->persistAll();
			return '1';
		} else {
			return '0';
		}
	}

	/**
	 * Update a given poi
	 * In this case the original cols for lat and lng were not set
	 *
	 * @param \JWeiland\Maps2\Domain\Model\PoiCollection $poiCollection The poiCollection to update
	 * @param float $lat Latitude
	 * @param float $lng Longitude
	 * @return \JWeiland\Maps2\Domain\Model\PoiCollection
	 */
	public function updateMarker(PoiCollection $poiCollection, $lat, $lng) {
		$poiCollection->setLatitude($lat);
		$poiCollection->setLongitude($lng);

		// save original poi position, when user has pressed "update" button for field address
		$poiCollection->setLatitudeOrig($lat);
		$poiCollection->setLongitudeOrig($lng);

		return $poiCollection;
	}

}