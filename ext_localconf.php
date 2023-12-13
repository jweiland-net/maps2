<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function (): void {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Maps2',
        'Maps2',
        [
            \JWeiland\Maps2\Controller\PoiCollectionController::class => 'show',
            \JWeiland\Maps2\Controller\AjaxController::class => 'process',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Maps2',
        'Overlay',
        [
            \JWeiland\Maps2\Controller\PoiCollectionController::class => 'overlay',
        ],
        // non-cacheable actions
        [
            \JWeiland\Maps2\Controller\PoiCollectionController::class => 'overlay',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Maps2',
        'SearchWithinRadius',
        [
            \JWeiland\Maps2\Controller\PoiCollectionController::class => 'search, listRadius',
        ],
        // non-cacheable actions
        [
            \JWeiland\Maps2\Controller\PoiCollectionController::class => 'listRadius',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'Maps2',
        'CityMap',
        [
            \JWeiland\Maps2\Controller\CityMapController::class => 'show, search',
        ],
        // non-cacheable actions
        [
            \JWeiland\Maps2\Controller\CityMapController::class => 'search',
        ]
    );

    // Activate caching for info window content
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_cachedhtml'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_cachedhtml'] = [
            'groups' => ['pages', 'all'],
        ];
    }

    // This is a solution to build GET forms.
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters']['maps2'] = 'tx_maps2_citymap[street]';

    // Create maps2 records while saving foreign records
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['createMaps2Record']
        = \JWeiland\Maps2\Hook\CreateMaps2RecordHook::class;

    // Move old flex form settings to new location before saving to DB
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['maps2MoveFlexFormFields']
        = \JWeiland\Maps2\Update\MoveOldFlexFormSettingsUpdate::class;
    // Migrate old POI record into configuration_map of poicollection table
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['maps2MigratePoiRecord']
        = \JWeiland\Maps2\Update\MigratePoiRecordsToConfigurationMapUpdate::class;
    // Migrate to new OSM Geocode URI in extension settings
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['maps2NewOsmGeocodeUriExtConf']
        = \JWeiland\Maps2\Update\NewGeocodeUriForOsmUpdate::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1530778687] = [
        'nodeName' => 'maps2InfoWindowContent',
        'priority' => 40,
        'class' => \JWeiland\Maps2\Form\FieldInformation\InfoWindowContent::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1633612058] = [
        'nodeName' => 'maps2ReadOnlyInputText',
        'priority' => 40,
        'class' => \JWeiland\Maps2\Form\Element\ReadOnlyInputTextElement::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeResolver'][1551448205] = [
        'nodeName' => 'maps2MapProvider',
        'priority' => 40,
        'class' => \JWeiland\Maps2\Form\Resolver\MapProviderResolver::class,
    ];

    // Add maps2 plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:maps2/Configuration/TsConfig/Page/ContentElementWizard.tsconfig">'
    );
});
