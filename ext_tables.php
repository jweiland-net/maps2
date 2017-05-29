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


\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_maps2_domain_model_poicollection', 'EXT:maps2/Resources/Private/Language/locallang_csh_tx_maps2_domain_model_poicollection.xlf');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_maps2_domain_model_poicollection');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_maps2_domain_model_poi');

