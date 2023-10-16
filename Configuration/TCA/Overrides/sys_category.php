<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function (): void {
    $extConf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \JWeiland\Maps2\Configuration\ExtConf::class
    );
    $mapHelper = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \JWeiland\Maps2\Helper\MapHelper::class,
        $extConf
    );
    $ll = 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:';
    $mapProvider = $mapHelper->getMapProvider();

    $newSysCategoryColumn = [
        'maps2_marker_icons' => [
            'exclude' => 1,
            'label' => $ll . 'sys_category.maps2_marker_icons.' . $mapProvider,
            'description' => $ll . 'sys_category.maps2_marker_icons.' . $mapProvider . '.description',
            'config' => [
                'type' => 'file',
                'minitems' => 0,
                'maxitems' => 1,
                'allowed' => 'common-image-types',
            ],
        ],
        'maps2_marker_icon_width' => [
            'exclude' => true,
            'label' => $ll . 'sys_category.maps2_marker_icon_width.' . $mapProvider,
            'description' => $ll . 'sys_category.maps2_marker_icon_width.' . $mapProvider . '.description',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'default' => 0,
                'eval' => 'trim',
            ],
        ],
        'maps2_marker_icon_height' => [
            'exclude' => true,
            'label' => $ll . 'sys_category.maps2_marker_icon_height.' . $mapProvider,
            'description' => $ll . 'sys_category.maps2_marker_icon_height.' . $mapProvider . '.description',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'default' => 0,
                'eval' => 'trim',
            ],
        ],
        'maps2_marker_icon_anchor_pos_x' => [
            'exclude' => true,
            'label' => $ll . 'sys_category.maps2_marker_icon_anchor_pos_x.' . $mapProvider,
            'description' => $ll . 'sys_category.maps2_marker_icon_anchor_pos_x.' . $mapProvider . '.description',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'default' => 0,
                'eval' => 'trim',
            ],
        ],
        'maps2_marker_icon_anchor_pos_y' => [
            'exclude' => true,
            'label' => $ll . 'sys_category.maps2_marker_icon_anchor_pos_y.' . $mapProvider,
            'description' => $ll . 'sys_category.maps2_marker_icon_anchor_pos_y.' . $mapProvider . '.description',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'default' => 0,
                'eval' => 'trim',
            ],
        ],
    ];

    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('sys_category', $newSysCategoryColumn);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
        'sys_category',
        '--div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tab.maps2.' . $mapHelper->getMapProvider() . ', maps2_marker_icons, maps2_marker_icon_width, maps2_marker_icon_height, maps2_marker_icon_anchor_pos_x, maps2_marker_icon_anchor_pos_y'
    );
});
