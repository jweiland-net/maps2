<?php
namespace JWeiland\Maps2\Domain\Model;

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

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RadiusResult {

	/**
	 * addressComponents
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\JWeiland\Maps2\Domain\Model\RadiusResult\AddressComponent>
	 */
	protected $addressComponents;

	/**
	 * formattedAddress
	 *
	 * @var string
	 */
	protected $formattedAddress;

	/**
	 * geometry
	 *
	 * @var \JWeiland\Maps2\Domain\Model\RadiusResult\Geometry
	 */
	protected $geometry;

	/**
	 * types
	 *
	 * @var array
	 */
	protected $types;

	/**
	 * poiCollections
	 *
	 * @var array
	 */
	protected $poiCollections;





	/**
	 * Setter for addressComponents
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $addressComponents
	 */
	public function setAddressComponents(\TYPO3\CMS\Extbase\Persistence\ObjectStorage $addressComponents) {
		$this->addressComponents = $addressComponents;
	}

	/**
	 * Getter for addressComponents
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getAddressComponents() {
		return $this->addressComponents;
	}

	/**
	 * Setter for formattedAddress
	 *
	 * @param string $formattedAddress
	 */
	public function setFormattedAddress($formattedAddress) {
		$this->formattedAddress = $formattedAddress;
	}

	/**
	 * Getter for formattedAddress
	 *
	 * @return string
	 */
	public function getFormattedAddress() {
		return $this->formattedAddress;
	}

	/**
	 * Setter for geometry
	 *
	 * @param \JWeiland\Maps2\Domain\Model\RadiusResult\Geometry $geometry
	 */
	public function setGeometry(\JWeiland\Maps2\Domain\Model\RadiusResult\Geometry $geometry) {
		$this->geometry = $geometry;
	}

	/**
	 * Getter for geometry
	 *
	 * @return \JWeiland\Maps2\Domain\Model\RadiusResult\Geometry
	 */
	public function getGeometry() {
		return $this->geometry;
	}

	/**
	 * Setter for Types
	 *
	 * @param array $types
	 */
	public function setTypes(array $types) {
		$this->types = $types;
	}

	/**
	 * Getter for types
	 *
	 * @return array
	 */
	public function getTypes() {
		return $this->types;
	}

	/**
	 * Setter for poiCollections
	 *
	 * @param array $poiCollections
	 */
	public function setPoiCollections(array $poiCollections) {
		$this->poiCollections = $poiCollections;
	}

	/**
	 * Getter for poiCollections
	 *
	 * @return array
	 */
	public function getPoiCollections() {
		return $this->poiCollections;
	}

}