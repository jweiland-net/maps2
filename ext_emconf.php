<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Maps2',
    'description' => 'Create maps with Marker, Area, Routes or Radius based on Google Maps or OpenStreetMap',
    'version' => '10.0.11',
    'category' => 'plugin',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'constraints' => [
        'depends' => [
            'php' => '7.4.0-8.99.99',
            'typo3' => '10.4.19-11.5.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
