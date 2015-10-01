<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'JWeiland.' . $_EXTKEY,
	'Maps2',
	array(
		'PoiCollection' => 'show',
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
		'PoiCollection' => 'search, checkForMultiple, listRadius',
	),
	// non-cacheable actions
	array(
		'PoiCollection' => 'checkForMultiple, listRadius',
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

if(TYPO3_MODE === 'BE') {
	if (\TYPO3\CMS\Core\Utility\GeneralUtility::compat_version('7.3')) {
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::registerAjaxHandler(
			'maps2Ajax',
			'JWeiland\\Maps2\\Dispatch\\AjaxRequest->dispatch',
			FALSE
		);
	} else {
		$GLOBALS['TYPO3_CONF_VARS']['BE']['AJAX']['maps2Ajax'] = 'JWeiland\\Maps2\\Dispatch\\AjaxRequest->dispatch';
	}
}

// activate caching for info window content
if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_cachedHtml'])) {
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['maps2_cachedHtml'] = array();
}

// This is a solution to build GET forms.
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_maps2_searchwithinradius[search][address]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_maps2_searchwithinradius[search][radius]';
$GLOBALS['TYPO3_CONF_VARS']['FE']['cacheHash']['excludedParameters'][] = 'tx_maps2_citymap[street]';

// add address before RTE for InfoWindow
$classRef = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('maps2') . 'Classes/Tca/InfoWindow.php';
$GLOBALS['T3_VAR']['getUserObj'][$classRef] = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('JWeiland\\Maps2\\Tca\\InfoWindow');
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getSingleFieldClass'][] = $classRef;