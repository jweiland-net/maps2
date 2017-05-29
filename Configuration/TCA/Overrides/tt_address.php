<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function() {
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {
        $tempColumns = array(
            'tx_maps2_uid' => array(
                'exclude' => 1,
                'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_uid',
                'config' => array(
                    'type' => 'group',
                    'internal_type' => 'db',
                    'allowed' => 'tx_maps2_domain_model_poicollection',
                    'prepend_tname' => false,
                    'show_thumbs' => false,
                    'size' => 1,
                    'maxitems' => 1,
                    'wizards' => array(
                        'suggest' => array(
                            'type' => 'suggest',
                            'default' => array(
                                'searchWholePhrase' => true
                            )
                        )
                    )
                )
            )
        );

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $tempColumns);
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'tx_maps2_uid');
    }
});
