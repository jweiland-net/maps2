<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_maps2_domain_model_poicollection',
    'EXT:maps2/Resources/Private/Language/locallang_csh_tx_maps2_domain_model_poicollection.xlf'
);

ExtensionManagementUtility::allowTableOnStandardPages('tx_maps2_domain_model_poicollection');
ExtensionManagementUtility::allowTableOnStandardPages('tx_maps2_domain_model_poi');
