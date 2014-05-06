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
class Poi extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {

	/**
	 * cruser_id
	 *
	 * @var integer
	 */
	protected $cruserId;

	/**
	 * Position Index
	 *
	 * @var integer
	 */
	protected $posIndex;

	/**
	 * Latitude
	 *
	 * @var float
	 */
	protected $latitude;

	/**
	 * Longitude
	 *
	 * @var float
	 */
	protected $longitude;





	/**
	 * Returns the cruserId
	 *
	 * @return float $cruserId
	 */
	public function getCruserId() {
		return $this->cruserId;
	}

	/**
	 * Sets the cruserId
	 *
	 * @param integer $cruserId
	 * @return void
	 */
	public function setCruserId($cruserId) {
		$this->cruserId = $cruserId;
	}

	/**
	 * Returns the latitude
	 *
	 * @return float $latitude
	 */
	public function getLatitude() {
		return $this->latitude;
	}

	/**
	 * Sets the latitude
	 *
	 * @param float $latitude
	 * @return void
	 */
	public function setLatitude($latitude) {
		$this->latitude = $latitude;
	}

	/**
	 * Returns the longitude
	 *
	 * @return float $longitude
	 */
	public function getLongitude() {
		return $this->longitude;
	}

	/**
	 * Sets the longitude
	 *
	 * @param float $longitude
	 * @return void
	 */
	public function setLongitude($longitude) {
		$this->longitude = $longitude;
	}

	/**
	 * Returns the pos_index
	 *
	 * @return integer $posIndex
	 */
	public function getPosIndex() {
		return $this->posIndex;
	}

	/**
	 * Sets the pos_index
	 *
	 * @param integer $posIndex
	 * @return void
	 */
	public function setPosIndex($posIndex) {
		$this->posIndex = $posIndex;
	}

}