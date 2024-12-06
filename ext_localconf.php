<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

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
        ],
        // non-cacheable actions
        [],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
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
        ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
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
        ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
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
        ],
        ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
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
});
