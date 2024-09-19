<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'ext-maps2-wizard-icon' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        'source' => 'EXT:maps2/Resources/Public/Icons/plugin_wizard.svg',
    ],
    'ext-maps2-record-type-point' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        'source' => 'EXT:maps2/Resources/Public/Icons/RecordTypePoint.png',
    ],
    'ext-maps2-record-type-area' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        'source' => 'EXT:maps2/Resources/Public/Icons/RecordTypeArea.png',
    ],
    'ext-maps2-record-type-route' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        'source' => 'EXT:maps2/Resources/Public/Icons/RecordTypeRoute.png',
    ],
    'ext-maps2-record-type-radius' => [
        'provider' => \TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider::class,
        'source' => 'EXT:maps2/Resources/Public/Icons/RecordTypeRadius.png',
    ],
];
