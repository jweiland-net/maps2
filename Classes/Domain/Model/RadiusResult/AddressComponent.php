<?php
namespace JWeiland\Maps2\Domain\Model\RadiusResult;

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

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class AddressComponent {

	/**
	 * longName
	 *
	 * @var string
	 */
	protected $longName;

	/**
	 * shortName
	 *
	 * @var string
	 */
	protected $shortName;

	/**
	 * types
	 *
	 * @var array
	 */
	protected $types;





	/**
	 * Setter for longName
	 *
	 * @param string $longName
	 */
	public function setLongName($longName) {
		$this->longName = $longName;
	}

	/**
	 * Getter for LongName
	 *
	 * @return string
	 */
	public function getLongName() {
		return $this->longName;
	}

	/**
	 * Setter for shortName
	 *
	 * @param string $shortName
	 */
	public function setShortName($shortName) {
		$this->shortName = $shortName;
	}

	/**
	 * Getter for ShortName
	 *
	 * @return string
	 */
	public function getShortName() {
		return $this->shortName;
	}

	/**
	 * Setter for types
	 *
	 * @param array $types
	 */
	public function setTypes($types) {
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

}