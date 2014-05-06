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
class Position {

	/**
	 * northeast
	 *
	 * @var \JWeiland\Maps2\Domain\Model\Location
	 */
	protected $northeast;

	/**
	 * southwest
	 *
	 * @var \JWeiland\Maps2\Domain\Model\Location
	 */
	protected $southwest;





	/**
	 * Setter for northeast
	 *
	 * @param \JWeiland\Maps2\Domain\Model\Location $northeast
	 */
	public function setNortheast(\JWeiland\Maps2\Domain\Model\Location $northeast) {
		$this->northeast = $northeast;
	}

	/**
	 * Getter for northeast
	 *
	 * @return \JWeiland\Maps2\Domain\Model\Location
	 */
	public function getNortheast() {
		return $this->northeast;
	}

	/**
	 * Setter for southwest
	 *
	 * @param \JWeiland\Maps2\Domain\Model\Location $southwest
	 */
	public function setSouthwest(\JWeiland\Maps2\Domain\Model\Location $southwest) {
		$this->southwest = $southwest;
	}

	/**
	 * Getter for southwest
	 *
	 * @return \\JWeiland\Maps2\Domain\Model\Location
	 */
	public function getSouthwest() {
		return $this->southwest;
	}

}