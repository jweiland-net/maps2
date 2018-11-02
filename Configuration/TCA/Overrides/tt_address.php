<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function() {
    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {
        $tempColumns = [
            'tx_maps2_uid' => [
                'exclude' => 1,
                'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_uid',
                'config' => [
                    'type' => 'group',
                    'internal_type' => 'db',
                    'allowed' => 'tx_maps2_domain_model_poicollection',
                    'prepend_tname' => false,
                    'size' => 1,
                    'maxitems' => 1,
                    'suggestOptions' => [
                        'default' => [
                            'searchWholePhrase' => true
                        ]
                    ]
                ]
            ]
        ];

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_address', $tempColumns);
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('tt_address', 'tx_maps2_uid');
    }
});
