<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Maps2',
    'description' => 'Create maps with Marker, Area, Routes or Radius based on Google Maps or OpenStreetMap',
    'version' => '9.2.1',
    'category' => 'plugin',
    'state' => 'stable',
    'uploadfolder' => false,
    'clearCacheOnLoad' => true,
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
        ],
    ],
];
