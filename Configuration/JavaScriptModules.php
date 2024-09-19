<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    'dependencies' => [
        'backend',
        'core',
    ],
    'imports' => [
        '@jweiland/maps2/' => [
            'path' => 'EXT:maps2/Resources/Public/JavaScript/',
            'exclude' => [
                'EXT:maps2/Resources/Public/JavaScript/leaflet.min.js',
            ],
        ],
        'leaflet' => 'EXT:maps2/Resources/Public/JavaScript/leaflet.min.js',
    ],
];
