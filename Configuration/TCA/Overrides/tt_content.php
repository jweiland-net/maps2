<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['maps2_maps2'] = 'select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['maps2_maps2'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'maps2_maps2',
    'FILE:EXT:maps2/Configuration/FlexForms/Maps2.xml'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['maps2_searchwithinradius'] = 'select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['maps2_searchwithinradius'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'maps2_searchwithinradius',
    'FILE:EXT:maps2/Configuration/FlexForms/Radius.xml'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['maps2_citymap'] = 'select_key';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['maps2_citymap'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'maps2_citymap',
    'FILE:EXT:maps2/Configuration/FlexForms/CityMap.xml'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JWeiland.maps2',
    'Maps2',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.maps.title'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JWeiland.maps2',
    'SearchWithinRadius',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.radius.title'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'JWeiland.maps2',
    'CityMap',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.cityMap.title'
);
