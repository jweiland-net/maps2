<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Maps2\Backend\Preview\Maps2PluginPreview;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['maps2_maps2'] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    'maps2_maps2',
    'FILE:EXT:maps2/Configuration/FlexForms/Maps2.xml',
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['maps2_searchwithinradius'] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    'maps2_searchwithinradius',
    'FILE:EXT:maps2/Configuration/FlexForms/Radius.xml',
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['maps2_citymap'] = 'pi_flexform';
ExtensionManagementUtility::addPiFlexFormValue(
    'maps2_citymap',
    'FILE:EXT:maps2/Configuration/FlexForms/CityMap.xml',
);

ExtensionUtility::registerPlugin(
    'maps2',
    'Maps2',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.maps.title',
);

ExtensionUtility::registerPlugin(
    'maps2',
    'SearchWithinRadius',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.radius.title',
);

ExtensionUtility::registerPlugin(
    'maps2',
    'CityMap',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.cityMap.title',
);

$GLOBALS['TCA']['tt_content']['types']['list']['previewRenderer']['maps2_maps2']
    = Maps2PluginPreview::class;

$GLOBALS['TCA']['tt_content']['types']['list']['previewRenderer']['maps2_searchwithinradius']
    = Maps2PluginPreview::class;

$GLOBALS['TCA']['tt_content']['types']['list']['previewRenderer']['maps2_citymap']
    = Maps2PluginPreview::class;
