<?php

declare(strict_types=1);

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

ExtensionUtility::registerPlugin(
    'maps2',
    'Maps2',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.maps2.title',
    'ext-maps2-wizard-icon',
    'plugins',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.maps2.description',
    'FILE:EXT:maps2/Configuration/FlexForms/Maps2.xml',
);

ExtensionUtility::registerPlugin(
    'maps2',
    'SearchWithinRadius',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.searchwithinradius.title',
    'ext-maps2-wizard-icon',
    'plugins',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.searchwithinradius.description',
    'FILE:EXT:maps2/Configuration/FlexForms/Radius.xml',
);

ExtensionUtility::registerPlugin(
    'maps2',
    'CityMap',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.citymap.title',
    'ext-maps2-wizard-icon',
    'plugins',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.citymap.description',
    'FILE:EXT:maps2/Configuration/FlexForms/CityMap.xml',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'pages;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:pages.ALT.list_formlabel,recursive',
    'maps2_maps2',
    'after:pi_flexform',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'pages;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:pages.ALT.list_formlabel,recursive',
    'maps2_searchwithinradius',
    'after:pi_flexform',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    'pages;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:pages.ALT.list_formlabel,recursive',
    'maps2_citymap',
    'after:pi_flexform',
);

$GLOBALS['TCA']['tt_content']['types']['maps2_maps2']['previewRenderer'] = Maps2PluginPreview::class;
$GLOBALS['TCA']['tt_content']['types']['maps2_searchwithinradius']['previewRenderer'] = Maps2PluginPreview::class;
$GLOBALS['TCA']['tt_content']['types']['maps2_citymap']['previewRenderer'] = Maps2PluginPreview::class;
