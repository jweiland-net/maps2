<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JWeiland.' . $_EXTKEY,
    'Maps2',
    array(
        'PoiCollection' => 'show, allowMap',
        'Ajax' => 'callAjaxObject',
    ),
    // non-cacheable actions
    array(
        'PoiCollection' => '',
        'Ajax' => 'callAjaxObject',
    )
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JWeiland.' . $_EXTKEY,
    'SearchWithinRadius',
    array(
        'PoiCollection' => 'search, multipleResults, listRadius',
    ),
    // non-cacheable actions
    array(
        'PoiCollection' => 'multipleResults, listRadius',
    )
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'JWeiland.' . $_EXTKEY,
    'CityMap',
    array(
        'CityMap' => 'show, search',
    ),
    // non-cacheable actions
    array(
        'CityMap' => 'search',
    )
);

if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
        'maps2Ajax',
        'JWeiland\\Maps2\\Dispatch\\AjaxRequest->dispatch'
    );
}

// activate caching for info window content
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_cachedhtml'])) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_cachedhtml'] = array(
        'groups' => array('pages', 'all')
    );
}

// This is a solution to build GET forms.
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_maps2_searchwithinradius[search][address]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_maps2_searchwithinradius[search][radius]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_maps2_citymap[street]';

// add address before RTE for InfoWindow
$classRef = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('maps2') . 'Classes/Tca/InfoWindow.php';
$GLOBALS['T3_VAR']['getUserObj'][$classRef] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('JWeiland\\Maps2\\Tca\\InfoWindow');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getSingleFieldClass'][] = $classRef;
unset($classRef);

// We have to save the permission to allow google requests before TS-Template rendering. It's needed by our own TS Condition object
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['initFEuser'][] = 'JWeiland\\Maps2\\Hook\\InitFeSessionHook->saveAllowGoogleRequestsInSession';

// this function is needed by a userFunc based TS-Condition
if (!function_exists('googleRequestsAreAllowed')) {
    function googleRequestsAreAllowed()
    {
        /** @var \JWeiland\Maps2\Condition\AllowGoogleRequestCondition $conditionMatcher */
        $conditionMatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('JWeiland\\Maps2\\Condition\\AllowGoogleRequestCondition');
        return $conditionMatcher->match();
    }
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
