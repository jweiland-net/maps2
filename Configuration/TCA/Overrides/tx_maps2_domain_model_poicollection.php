<?php

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\MapHelper;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function (): void {
    $extConf = GeneralUtility::makeInstance(
        ExtConf::class,
        GeneralUtility::makeInstance(ExtensionConfiguration::class)
    );
    $mapHelper = GeneralUtility::makeInstance(MapHelper::class, $extConf);

    // Set a default for column map_provider on save
    $GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['map_provider']['config']['default']
        = $mapHelper->getMapProvider();

    // Set default for poi collection type
    $GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['collection_type']['config']['default']
        = $extConf->getDefaultMapType();

    // Set latitude/longitude to float representation of extension configuration
    $GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['latitude']['config']['default'] = number_format(
        $extConf->getDefaultLatitude(),
        6
    );
    $GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['longitude']['config']['default'] = number_format(
        $extConf->getDefaultLongitude(),
        6
    );

    // If both map providers are allowed in ExtensionManager we have to add a selectbox for map provider to TCA
    if ($extConf->getMapProvider() === 'both') {
        ExtensionManagementUtility::addToAllTCAtypes(
            'tx_maps2_domain_model_poicollection',
            'map_provider',
            '',
            'before:configuration_map'
        );
    }
});
