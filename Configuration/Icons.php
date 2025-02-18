<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;
use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    'ext-maps2-wizard-icon' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:maps2/Resources/Public/Icons/plugin_wizard.svg',
    ],
    'ext-maps2-record-type-point' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:maps2/Resources/Public/Icons/RecordTypePoint.png',
    ],
    'ext-maps2-record-type-area' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:maps2/Resources/Public/Icons/RecordTypeArea.png',
    ],
    'ext-maps2-record-type-route' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:maps2/Resources/Public/Icons/RecordTypeRoute.png',
    ],
    'ext-maps2-record-type-radius' => [
        'provider' => BitmapIconProvider::class,
        'source' => 'EXT:maps2/Resources/Public/Icons/RecordTypeRadius.png',
    ],
];
