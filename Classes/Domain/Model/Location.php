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
class Location {

	/**
	 * latitude
	 *
	 * @var float
	 */
	protected $lat;

	/**
	 * longitude
	 *
	 * @var float
	 */
	protected $lng;





	/**
	 * Setter for lat
	 *
	 * @param float $lat
	 */
	public function setLat($lat) {
		$this->lat = (float) $lat;
	}

	/**
	 * Getter for lat
	 *
	 * @return float
	 */
	public function getLat() {
		return $this->lat;
	}

	/**
	 * Setter for lng
	 *
	 * @param float $lng
	 */
	public function setLng($lng) {
		$this->lng = (float) $lng;
	}

	/**
	 * Getter for lng
	 *
	 * @return float
	 */
	public function getLng() {
		return $this->lng;
	}

	/**
	 * Getter for latitude/lat
	 * Wrapper for getLat()
	 *
	 * @return float
	 */
	public function getLatitude() {
		return $this->lat;
	}

	/**
	 * Getter for longitude/lng
	 * Wrapper for getLng()
	 *
	 * @return float
	 */
	public function getLongitude() {
		return $this->lng;
	}

}