<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$_EXTCONF = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['maps2']);

$GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['latitude']['config']['default'] = number_format((float)$_EXTCONF['defaultLatitude'], 6);
$GLOBALS['TCA']['tx_maps2_domain_model_poicollection']['columns']['longitude']['config']['default'] = number_format((float)$_EXTCONF['defaultLongitude'], 6);
