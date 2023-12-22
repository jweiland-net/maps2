<?php

use JWeiland\Maps2\Configuration\ExtConf;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function (): void {
    ExtensionManagementUtility::addStaticFile(
        'maps2',
        'Configuration/TypoScript',
        'Maps2 Default'
    );

    $extConf = GeneralUtility::makeInstance(
        ExtConf::class,
        GeneralUtility::makeInstance(ExtensionConfiguration::class)
    );
    if ($extConf->getMapProvider() === 'both' || $extConf->getMapProvider() === 'gm') {
        ExtensionManagementUtility::addStaticFile(
            'maps2',
            'Configuration/TypoScript/GoogleMaps',
            'Maps2 for Google Maps'
        );
    }

    if ($extConf->getMapProvider() === 'both' || $extConf->getMapProvider() === 'osm') {
        ExtensionManagementUtility::addStaticFile(
            'maps2',
            'Configuration/TypoScript/OpenStreetMap',
            'Maps2 for Open Street Map'
        );
    }
});
