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
            'Ajax' => 'process',
        ],
        // non-cacheable actions
        [
            'PoiCollection' => '',
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

    // This is a solution to build GET forms.
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_maps2_citymap[street]';
    // Create maps2 records while saving foreign records
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = \JWeiland\Maps2\Hook\CreateMaps2RecordHook::class;
    // Move old flex form settings to new location before saving to DB
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/install']['update']['maps2MoveFlexFormFields'] = \JWeiland\Maps2\Update\MoveOldFlexFormSettingsUpdate::class;

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1530869394] = [
        'nodeName' => 'maps2ReadOnly',
        'priority' => 40,
        'class' => \JWeiland\Maps2\Form\Element\ReadOnlyInputTextElement::class,
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1530778687] = [
        'nodeName' => 'maps2InfoWindowContent',
        'priority' => 40,
        'class' => \JWeiland\Maps2\Form\Element\InfoWindowCkEditorElement::class,
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
