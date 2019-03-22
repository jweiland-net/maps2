<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function() {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        'maps2',
        'Configuration/TypoScript',
        'Maps2 Default'
    );

    $extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JWeiland\Maps2\Configuration\ExtConf::class);
    if ($extConf->getMapProvider() === 'both' || $extConf->getMapProvider() === 'gm') {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            'maps2',
            'Configuration/TypoScript/GoogleMaps',
            'Maps2 for Google Maps'
        );
    }
    if ($extConf->getMapProvider() === 'both' || $extConf->getMapProvider() === 'osm') {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
            'maps2',
            'Configuration/TypoScript/OpenStreetMap',
            'Maps2 for Open Street Map'
        );
    }
});
