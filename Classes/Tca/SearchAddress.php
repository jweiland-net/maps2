<?php
namespace JWeiland\Maps2\Tca;

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
class SearchAddress {

	/**
	 * create a button to search for the given address
	 *
	 * @param array $PA parent Array
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $fObj parent object
	 * @return string
	 */
	public function searchAddress(array $PA, \TYPO3\CMS\Backend\Form\FormEngine $fObj) {
		$onClick = 'TxMaps2.findAddressOnMap()';
		$buttonUpdate = '<input type="button" value="Update" onclick="' . $onClick . '">';

		$onClick = 'TxMaps2.resetMarkerToAddress(' . $PA['row']['latitude_orig'] . ', ' . $PA['row']['longitude_orig'] . ')';
		$buttonReset = '<input type="button" value="Reset" onclick="' . $onClick . '">';

		return $buttonUpdate . $buttonReset;
	}

}