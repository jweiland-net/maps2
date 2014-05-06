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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @package maps2
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class InfoWindow {

	/**
	 * add address information before RTE
	 *
	 * @param string $table The table name
	 * @param string $field The field name
	 * @param array $row The current record
	 * @param mixed $out String (normal) or array (palettes)
	 * @param array $PA The field parameter array
	 * @param \TYPO3\CMS\Backend\Form\FormEngine $pObj The parent object
	 * @return string
	 */
	public function getSingleField_postProcess($table, $field, array $row, &$out, array $PA, \TYPO3\CMS\Backend\Form\FormEngine $pObj) {
		if ($table === 'tx_maps2_domain_model_poicollection' && $field === 'info_window_content') {
			$address = GeneralUtility::trimExplode(',', $row['address']);
			$addressHeader = $GLOBALS['LANG']->sL('LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.info_window_address');
			$content = '
				<tr class="class-main12">
					<td colspan="2" class="formField-header">
						<span class="class-main14"><strong>' . $addressHeader . '</strong></span>
						<div id="infoWindowAddress">' . implode('<br />', $address) . '</div>
					</td>
				</tr>
			';
			$out = $content . $out;
		}
	}

}