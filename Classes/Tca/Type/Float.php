<?php
namespace JWeiland\Maps2\Tca\Type;

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
class Float {

	/**
	 * This method returns js code for validating float dataTypes
	 *
	 * @return string
	 */
	function returnFieldJS() {
		return '
			return value;
		';
	}

	/**
	 * This method converts the value into dataType float
	 *
	 * @param string $value
	 * @param string $is_in
	 * @param string $set
	 * @return string
	 */
	function evaluateFieldValue($value, $is_in, &$set) {
		$floatValue = number_format((float)$value, 6);
		//var_dump($floatValue);
		return $floatValue;
	}

}