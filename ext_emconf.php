<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Maps2',
    'description' => 'Create maps with Marker, Area, Routes or Radius based on Google Maps or OpenStreetMap',
    'version' => '11.0.0',
    'category' => 'plugin',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.8-12.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'static_info_tables' => '12.4.0-12.99.99',
        ],
    ],
];
