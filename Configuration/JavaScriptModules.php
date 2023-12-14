<?php

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
