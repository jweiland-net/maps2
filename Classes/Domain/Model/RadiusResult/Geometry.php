<?php
namespace JWeiland\Maps2\Domain\Model\RadiusResult;

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
class Geometry {

	/**
	 * bounds
	 *
	 * @var \JWeiland\Maps2\Domain\Model\Position
	 */
	protected $bounds;

	/**
	 * location
	 *
	 * @var \JWeiland\Maps2\Domain\Model\Location
	 */
	protected $location;

	/**
	 * locationType
	 *
	 * @var string
	 */
	protected $locationType;

	/**
	 * viewport
	 *
	 * @var \JWeiland\Maps2\Domain\Model\Position
	 */
	protected $viewport;





	/**
	 * Setter for bounds
	 *
	 * @param \JWeiland\Maps2\Domain\Model\Position $bounds
	 */
	public function setBounds(\JWeiland\Maps2\Domain\Model\Position $bounds) {
		$this->bounds = $bounds;
	}

	/**
	 * Getter for bounds
	 *
	 * @return \JWeiland\Maps2\Domain\Model\Position
	 */
	public function getBounds() {
		return $this->bounds;
	}

	/**
	 * Setter for location
	 *
	 * @param \JWeiland\Maps2\Domain\Model\Location $location
	 */
	public function setLocation(\JWeiland\Maps2\Domain\Model\Location $location) {
		$this->location = $location;
	}

	/**
	 * Getter for location
	 *
	 * @return \JWeiland\Maps2\Domain\Model\Location
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * Setter for locationType
	 *
	 * @param string $locationType
	 */
	public function setLocationType($locationType) {
		$this->locationType = $locationType;
	}

	/**
	 * Getter for locationType
	 *
	 * @return string
	 */
	public function getLocationType() {
		return $this->locationType;
	}

	/**
	 * Setter for viewport
	 *
	 * @param \JWeiland\Maps2\Domain\Model\Position $viewport
	 */
	public function setViewport(\JWeiland\Maps2\Domain\Model\Position $viewport) {
		$this->viewport = $viewport;
	}

	/**
	 * Getter for viewport
	 *
	 * @return \JWeiland\Maps2\Domain\Model\Position
	 */
	public function getViewport() {
		return $this->viewport;
	}

}