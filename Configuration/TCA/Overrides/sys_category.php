<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

call_user_func(function() {
    $ll = 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:';

    $newSysCategoryColumn = array(
        'marker_icon' => array(
            'exclude' => 1,
            'label' => $ll . 'sys_category.marker_icon',
            'config' => array(
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim'
            )
        ),
    );

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_category', $newSysCategoryColumn);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('sys_category', '--div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tab.maps2, marker_icon');
});
