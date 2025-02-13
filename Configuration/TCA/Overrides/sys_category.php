<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Maps2\Helper\MapHelper;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

call_user_func(static function (): void {
    $mapHelper = GeneralUtility::makeInstance(MapHelper::class);

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
                'type' => 'number',
                'format' => 'integer',
            ],
        ],
        'maps2_marker_icon_height' => [
            'exclude' => true,
            'label' => $ll . 'sys_category.maps2_marker_icon_height.' . $mapProvider,
            'description' => $ll . 'sys_category.maps2_marker_icon_height.' . $mapProvider . '.description',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
            ],
        ],
        'maps2_marker_icon_anchor_pos_x' => [
            'exclude' => true,
            'label' => $ll . 'sys_category.maps2_marker_icon_anchor_pos_x.' . $mapProvider,
            'description' => $ll . 'sys_category.maps2_marker_icon_anchor_pos_x.' . $mapProvider . '.description',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
            ],
        ],
        'maps2_marker_icon_anchor_pos_y' => [
            'exclude' => true,
            'label' => $ll . 'sys_category.maps2_marker_icon_anchor_pos_y.' . $mapProvider,
            'description' => $ll . 'sys_category.maps2_marker_icon_anchor_pos_y.' . $mapProvider . '.description',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
            ],
        ],
    ];

    ExtensionManagementUtility::addTCAcolumns('sys_category', $newSysCategoryColumn);
    ExtensionManagementUtility::addToAllTCAtypes(
        'sys_category',
        '--div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tab.maps2.' . $mapHelper->getMapProvider() . ', maps2_marker_icons, maps2_marker_icon_width, maps2_marker_icon_height, maps2_marker_icon_anchor_pos_x, maps2_marker_icon_anchor_pos_y',
    );
});
