<?php
namespace JWeiland\Maps2\Tca;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class InfoWindow
 *
 * @category Tca
 * @package  Maps2
 * @author   Stefan Froemken <projects@jweiland.net>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License
 * @link     https://github.com/jweiland-net/maps2
 */
class InfoWindow
{
    /**
     * add address information before RTE
     *
     * @param string $table The table name
     * @param string $field The field name
     * @param array $row The current record
     * @param mixed $out String (normal) or array(palettes)
     * @param array $PA The field parameter array
     * @param \TYPO3\CMS\Backend\Form\FormEngine $pObj The parent object
     * @return string
     */
    public function getSingleField_postProcess($table, $field, array $row, &$out, array $PA, \TYPO3\CMS\Backend\Form\FormEngine $pObj)
    {
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
