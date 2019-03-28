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

    // If both map providers are allowed in ExtensionManager we have to add a selectbox for map provider to TCA
    if ($extConf->getMapProvider() === 'both') {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
            'tx_maps2_domain_model_poicollection',
            'map_provider',
            '',
            'before:configuration_map'
        );
    }

    // Set a default for column map_provider on save
    $mapService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JWeiland\Maps2\Service\MapService::class);
    $GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['map_provider']['config']['default'] = $mapService->getMapProvider();

    // Add column "categories" to tx_maps2_domain_model_poicollection table
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::makeCategorizable(
        'maps2',
        'tx_maps2_domain_model_poicollection',
        'categories',
        []
    );
});
