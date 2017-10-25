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

// add maps2 plugin to new element wizard
if (version_compare(TYPO3_branch, '6.3.0', '<') ) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:maps2/Configuration/TSconfig/ContentElementWizard62.txt">');
} else {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:maps2/Configuration/TSconfig/ContentElementWizard.txt">');
}
