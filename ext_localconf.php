<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$boot = function() {
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
            'PoiCollection' => 'search, multipleResults, listRadius',
        ],
        // non-cacheable actions
        [
            'PoiCollection' => 'multipleResults, listRadius',
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
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_maps2_searchwithinradius[search][address]';
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_maps2_searchwithinradius[search][radius]';
    $GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_maps2_citymap[street]';

    // add address before RTE for InfoWindow
    $classRef = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('maps2') . 'Classes/Tca/InfoWindow.php';
    $GLOBALS['T3_VAR']['getUserObj'][$classRef] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\JWeiland\Maps2\Tca\InfoWindow::class);
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getSingleFieldClass'][] = $classRef;

    // We have to save the permission to allow google requests before TS-Template rendering. It's needed by our own TS Condition object
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'][] = \JWeiland\Maps2\Hook\InitFeSessionHook::class . '->saveAllowGoogleRequestsInSession';

    // Register SVG Icon Identifier
    $svgIcons = [
        'ext-maps2-wizard-icon' => 'plugin_wizard.svg',
    ];
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    foreach ($svgIcons as $identifier => $fileName) {
        $iconRegistry->registerIcon(
            $identifier,
            \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
            ['source' => 'EXT:maps2/Resources/Public/Icons/' . $fileName]
        );
    }

    // add maps2 plugin to new element wizard
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:maps2/Configuration/TSconfig/ContentElementWizard.txt">');
};

$boot();
unset($boot);
