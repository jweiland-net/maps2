<?php
if (!defined('TYPO3')) {
    die('Access denied.');
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_maps2_domain_model_poicollection',
    'EXT:maps2/Resources/Private/Language/locallang_csh_tx_maps2_domain_model_poicollection.xlf'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_maps2_domain_model_poicollection');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_maps2_domain_model_poi');
