<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function() {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'JWeiland.maps2',
        'Maps2',
        [
            'PoiCollection' => 'show, allowMap',
            'Ajax' => 'callAjaxObject',
        ],
        // non-cacheable actions
        [
            'PoiCollection' => '',
            'Ajax' => 'callAjaxObject',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'JWeiland.maps2',
        'SearchWithinRadius',
        [
            'PoiCollection' => 'search, listRadius',
        ],
        // non-cacheable actions
        [
            'PoiCollection' => 'listRadius',
        ]
    );

    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        'JWeiland.maps2',
        'CityMap',
        [
            'CityMap' => 'show, search',
        ],
        // non-cacheable actions
        [
            'CityMap' => 'search',
        ]
    );

    // activate caching for info window content
    if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_cachedhtml'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_cachedhtml'] = [
            'groups' => ['pages', 'all']
        ];
    }

    // Register cache to store registry entries of foreign extensions
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_registry'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
        'groups' => ['all', 'system']
    ];

    // This is a solution to build GET forms.
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_maps2_citymap[street]';

    // We have to save the permission to allow map provider requests before TS-Template rendering. It's needed by our own TS Condition object
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'][] = \JWeiland\Maps2\Hook\InitFeSessionHook::class . '->saveAllowGoogleRequestsInSession';
    // Create maps2 records while saving foreign records
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \JWeiland\Maps2\Hook\CreateMaps2RecordHook::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeResolver'][1530869394] = [
        'nodeName' => 'maps2ReadOnly',
        'priority' => 40,
        'class' => \JWeiland\Maps2\Form\Resolver\ReadOnlyInputTextResolver::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeResolver'][1530778687] = [
        'nodeName' => 'maps2InfoWindowContent',
        'priority' => 40,
        'class' => \JWeiland\Maps2\Form\Resolver\InfoWindowContentResolver::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeResolver'][1551448205] = [
        'nodeName' => 'maps2MapProvider',
        'priority' => 40,
        'class' => \JWeiland\Maps2\Form\Resolver\MapProviderResolver::class,
    ];

    // Register SVG Icon Identifier
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $svgIcons = [
        'ext-maps2-wizard-icon' => 'plugin_wizard.svg',
    ];
    foreach ($svgIcons as $identifier => $fileName) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:maps2/Resources/Public/Icons/' . $fileName]
        );
    }
    $bmpIcons = [
        'ext-maps2-record-type-point' => 'RecordTypePoint.png',
        'ext-maps2-record-type-area' => 'RecordTypeArea.png',
        'ext-maps2-record-type-route' => 'RecordTypeRoute.png',
        'ext-maps2-record-type-radius' => 'RecordTypeRadius.png',
    ];
    foreach ($bmpIcons as $identifier => $fileName) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
            ['source' => 'EXT:maps2/Resources/Public/Icons/' . $fileName]
        );
    }

    // add maps2 plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:maps2/Configuration/TSconfig/ContentElementWizard.txt">');

    $signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
    $signalSlotDispatcher->connect(
        \TYPO3\CMS\Install\Service\SqlExpectedSchemaService::class,
        'tablesDefinitionIsBeingBuilt',
        \JWeiland\Maps2\Tca\Maps2Registry::class,
        'addMaps2DatabaseSchemasToTablesDefinition'
    );
    if (!(TYPO3_REQUESTTYPE & TYPO3_REQUESTTYPE_INSTALL)) {
        $signalSlotDispatcher->connect(
            \TYPO3\CMS\Extensionmanager\Utility\InstallUtility::class,
            'tablesDefinitionIsBeingBuilt',
            \JWeiland\Maps2\Tca\Maps2Registry::class,
            'addMaps2DatabaseSchemaToTablesDefinition'
        );
    }
});
