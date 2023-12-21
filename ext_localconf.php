<?php

use JWeiland\Maps2\Controller\AjaxController;
use JWeiland\Maps2\Controller\CityMapController;
use JWeiland\Maps2\Controller\PoiCollectionController;
use JWeiland\Maps2\Form\Element\ReadOnlyInputTextElement;
use JWeiland\Maps2\Form\FieldInformation\InfoWindowContent;
use JWeiland\Maps2\Form\Resolver\MapProviderResolver;
use JWeiland\Maps2\Hook\CreateMaps2RecordHook;
use JWeiland\Maps2\Update\MigratePoiRecordsToConfigurationMapUpdate;
use JWeiland\Maps2\Update\MoveOldFlexFormSettingsUpdate;
use JWeiland\Maps2\Update\NewGeocodeUriForOsmUpdate;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function (): void {
    ExtensionUtility::configurePlugin(
        'Maps2',
        'Maps2',
        [
            PoiCollectionController::class => 'show',
            AjaxController::class => 'process',
        ]
    );

    ExtensionUtility::configurePlugin(
        'Maps2',
        'Overlay',
        [
            PoiCollectionController::class => 'overlay',
        ],
        // non-cacheable actions
        [
            PoiCollectionController::class => 'overlay',
        ]
    );

    ExtensionUtility::configurePlugin(
        'Maps2',
        'SearchWithinRadius',
        [
            PoiCollectionController::class => 'search, listRadius',
        ],
        // non-cacheable actions
        [
            PoiCollectionController::class => 'listRadius',
        ]
    );

    ExtensionUtility::configurePlugin(
        'Maps2',
        'CityMap',
        [
            CityMapController::class => 'show, search',
        ],
        // non-cacheable actions
        [
            CityMapController::class => 'search',
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
        = CreateMaps2RecordHook::class;

    // Move old flex form settings to new location before saving to DB
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['maps2MoveFlexFormFields']
        = MoveOldFlexFormSettingsUpdate::class;
    // Migrate old POI record into configuration_map of poicollection table
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['maps2MigratePoiRecord']
        = MigratePoiRecordsToConfigurationMapUpdate::class;
    // Migrate to new OSM Geocode URI in extension settings
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['maps2NewOsmGeocodeUriExtConf']
        = NewGeocodeUriForOsmUpdate::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1530778687] = [
        'nodeName' => 'maps2InfoWindowContent',
        'priority' => 40,
        'class' => InfoWindowContent::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1633612058] = [
        'nodeName' => 'maps2ReadOnlyInputText',
        'priority' => 40,
        'class' => ReadOnlyInputTextElement::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeResolver'][1551448205] = [
        'nodeName' => 'maps2MapProvider',
        'priority' => 40,
        'class' => MapProviderResolver::class,
    ];

    // Add maps2 plugin to new element wizard
    ExtensionManagementUtility::addPageTSConfig(
        '<INCLUDE_TYPOSCRIPT: source="FILE:EXT:maps2/Configuration/TsConfig/Page/ContentElementWizard.tsconfig">'
    );
});
