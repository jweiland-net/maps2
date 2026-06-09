<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

if (!defined('TYPO3')) {
    die('Access denied.');
}

$newSysCategoryColumn = [
    'maps2_marker_icons' => [
        'config' => [
            'type' => 'file',
            'minitems' => 0,
            'maxitems' => 1,
            'allowed' => 'common-image-types',
        ],
    ],
    'maps2_marker_icon_width' => [
        'config' => [
            'type' => 'number',
            'format' => 'integer',
        ],
    ],
    'maps2_marker_icon_height' => [
        'config' => [
            'type' => 'number',
            'format' => 'integer',
        ],
    ],
    'maps2_marker_icon_anchor_pos_x' => [
        'config' => [
            'type' => 'number',
            'format' => 'integer',
        ],
    ],
    'maps2_marker_icon_anchor_pos_y' => [
        'config' => [
            'type' => 'number',
            'format' => 'integer',
        ],
    ],
];

ExtensionManagementUtility::addTCAcolumns('sys_category', $newSysCategoryColumn);
