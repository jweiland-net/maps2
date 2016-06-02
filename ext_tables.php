<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $_EXTKEY,
    'Maps2',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.maps.title'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $_EXTKEY,
    'SearchWithinRadius',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.radius.title'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    $_EXTKEY,
    'CityMap',
    'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:plugin.cityMap.title'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'Maps2');

$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY);
$pluginSignature = strtolower($extensionName) . '_maps2';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Maps2.xml');

$pluginSignature = strtolower($extensionName) . '_searchwithinradius';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/Radius.xml');

$pluginSignature = strtolower($extensionName) . '_citymap';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist'][$pluginSignature] = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/CityMap.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_maps2_domain_model_poicollection', 'EXT:maps2/Resources/Private/Language/locallang_csh_tx_maps2_domain_model_poicollection.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_maps2_domain_model_poicollection');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_maps2_domain_model_poi');

// Default items for tt_address
$tempColumns = array(
    'tx_maps2_uid' => array(
        'exclude' => 1,
        'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_uid',
        'config' => array(
            'type' => 'group',
            'internal_type' => 'db',
            'allowed' => 'tx_maps2_domain_model_poicollection',
            'prepend_tname' => false,
            'show_thumbs' => false,
            'size' => 1,
            'maxitems' => 1,
            'wizards' => array(
                'suggest' => array(
                    'type' => 'suggest',
                    'default' => array(
                        'searchWholePhrase' => true
                    )
                )
            )
        )
    )
);

// add fields for tt_address
$table = 'tt_address';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns($table, $tempColumns, 1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes($table, 'tx_maps2_uid');

// add categories field to poicollection table
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
    $_EXTKEY,
    'tx_maps2_domain_model_poicollection',
    'categories',
    array()
);
