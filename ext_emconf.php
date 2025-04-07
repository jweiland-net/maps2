<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Maps2',
    'description' => 'Create maps with Marker, Area, Routes or Radius based on Google Maps or OpenStreetMap',
    'version' => '11.0.7',
    'category' => 'plugin',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'Stefan Froemken',
    'author_email' => 'projects@jweiland.net',
    'author_company' => 'jweiland.net',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.25-12.4.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'static_info_tables' => '12.4.0-12.99.99',
        ],
    ],
];
