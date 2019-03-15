<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function() {
    $extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \JWeiland\Maps2\Configuration\ExtConf::class
    );

    // Set latitude/longitude to float representation of extension configuration
    $GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['latitude']['config']['default'] = number_format(
        $extConf->getDefaultLatitude(),
        6
    );
    $GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['longitude']['config']['default'] = number_format(
        $extConf->getDefaultLongitude(),
        6
    );

    // Set default of map_provider to pre configured map provider of Extension Manager Configuration
    if ($extConf->getMapProvider() === 'both') {
        $mapProvider = $extConf->getDefaultMapProvider();
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'tx_maps2_domain_model_poicollection',
            'map_provider',
            '',
            'before:configuration_map'
        );
    } else {
        $mapProvider = $extConf->getMapProvider();
    }
    $GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['map_provider']['config']['default'] = $mapProvider;

    // Remove unsupported collection_types if map provider is OSM
    if ($mapProvider === 'osm') {
        unset($GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['collection_type']['config']['items'][2]);
        unset($GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['collection_type']['config']['items'][3]);
        //unset($GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['collection_type']['config']['items'][4]);
    }

    // Add column "categories" to tx_maps2_domain_model_poicollection table
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        'maps2',
        'tx_maps2_domain_model_poicollection',
        'categories',
        []
    );
});
