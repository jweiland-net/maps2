<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(static function (): void {
    $extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \JWeiland\Maps2\Configuration\ExtConf::class
    );
    $mapHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \JWeiland\Maps2\Helper\MapHelper::class,
        $extConf
    );

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
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'tx_maps2_domain_model_poicollection',
            'map_provider',
            '',
            'before:configuration_map'
        );
    }
});
