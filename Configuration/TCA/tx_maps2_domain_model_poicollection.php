<?php
return [
    'ctrl' => [
        'title' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection',
        'label' => 'title',
        'label_alt' => 'address',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'cruser_id' => 'cruser_id',
        'dividers2tabs' => true,
        'default_sortby' => 'ORDER BY title',
        'type' => 'collection_type',
        'versioningWS' => 2,
        'versioning_followPages' => true,
        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'title,pois',
        'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('maps2') . 'Resources/Public/Icons/tx_maps2_domain_model_poicollection.gif'
    ],
    'interface' => [
        'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, collection_type, title, address, latitude, longitude, latitude_orig, longitude_orig, configuration_map, pois, stroke_color, stroke_opacity, stroke_weight, fill_color, fill_opacity, info_window_content',
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'special' => 'languages',
                'items' => [
                    [
                        'LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages',
                        -1,
                        'flags-multiple'
                    ],
                ],
                'default' => 0,
            ]
        ],
        'l10n_parent' => [
            'exclude' => 1,
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        '',
                        0
                    ]
                ],
                'foreign_table' => 'tt_content',
                'foreign_table_where' => 'AND tt_content.pid=###CURRENT_PID### AND tt_content.sys_language_uid IN (-1,0)',
                'default' => 0
            ]
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => ''
            ]
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => '30',
                'max' => '255'
            ]
        ],
        'hidden' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
            'config' => [
                'type' => 'check',
                'items' => [
                    '1' => [
                        '0' => 'LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:hidden.I.0'
                    ]
                ]
            ]
        ],
        'starttime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                'size' => '13',
                'eval' => 'datetime',
                'default' => 0
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly'
        ],
        'endtime' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                'size' => '13',
                'eval' => 'datetime',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038)
                ]
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly'
        ],
        'collection_type' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 'Empty',
                'eval' => 'required',
                'showIconTable' => true,
                'items' => [
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.empty',
                        'Empty'
                    ],
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.point',
                        'Point',
                        'EXT:maps2/Resources/Public/Icons/TypeSelectPointSmall.png'
                    ],
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.area',
                        'Area',
                        'EXT:maps2/Resources/Public/Icons/TypeSelectAreaSmall.png'
                    ],
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.route',
                        'Route',
                        'EXT:maps2/Resources/Public/Icons/TypeSelectRouteSmall.png'
                    ],
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.radius',
                        'Radius',
                        'EXT:maps2/Resources/Public/Icons/TypeSelectRadiusSmall.png'
                    ],
                ],
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => false,
                    ],
                ],
            ],
        ],
        'title' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim,required'
            ],
        ],
        'address' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.address',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'eval' => 'trim',
                'wizards' => [
                    'searchAddress' => [
                        'type' => 'userFunc',
                        'userFunc' => \JWeiland\Maps2\Tca\SearchAddress::class . '->searchAddress',
                    ],
                ],
            ],
        ],
        'latitude' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.latitude',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => \JWeiland\Maps2\Tca\Type\FloatType::class,
            ],
        ],
        'longitude' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.longitude',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => \JWeiland\Maps2\Tca\Type\FloatType::class,
            ],
        ],
        'latitude_orig' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.latitudeOrig',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'readOnly' => true,
            ],
        ],
        'longitude_orig' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.longitudeOrig',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'readOnly' => true,
            ],
        ],
        'configuration_map' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.configuration_map',
            'config' => [
                'type' => 'user',
                'userFunc' => \JWeiland\Maps2\Tca\GoogleMaps::class . '->render',
            ],
        ],
        'radius' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.radius',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => 'trim,int',
            ],
        ],
        'pois' => [
            'exclude' => 1,
            'config' => [
                'type' => 'passthrough',
                'foreign_table' => 'tx_maps2_domain_model_poi',
                'foreign_field' => 'poicollection',
            ],
        ],
        'stroke_color' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.stroke_color',
            'config' => [
                'type' => 'input',
                'size' => 7,
                'eval' => 'trim',
            ],
        ],
        'stroke_opacity' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.stroke_opacity',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'eval' => 'trim',
            ],
        ],
        'stroke_weight' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.stroke_weight',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'eval' => 'trim',
            ],
        ],
        'fill_color' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.fill_color',
            'config' => [
                'type' => 'input',
                'size' => 7,
                'eval' => 'trim',
            ],
        ],
        'fill_opacity' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.fill_opacity',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'eval' => 'trim',
            ],
        ],
        'info_window_content' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.info_window_content',
            'config' => [
                'type' => 'text',
                'cols' => 40,
                'rows' => 15,
                'eval' => 'trim',
                'default' => '',
            ],
            'defaultExtras' => 'richtext[]',
        ],
    ],
    'types' => [
        'Empty' => ['showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden, collection_type, title,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime, endtime'],
        'Point' => ['showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden, collection_type, title, address;;1, configuration_map, pois,--div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.info_window,info_window_content;;;richtext[]:rte_transform[mode=ts_css],--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime, endtime'],
        'Area' => ['showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden, collection_type, title, latitude, longitude, configuration_map, pois,--div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.style, stroke_color, stroke_opacity, stroke_weight, fill_color, fill_opacity,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime, endtime'],
        'Route' => ['showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden, collection_type, title, latitude, longitude, configuration_map, pois,--div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.style, stroke_color, stroke_opacity, stroke_weight, fill_color, fill_opacity,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime, endtime'],
        'Radius' => ['showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden, collection_type, title, address;;2, configuration_map, pois,--div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xml:tx_maps2_domain_model_poicollection.style, stroke_color, stroke_opacity, stroke_weight, fill_color, fill_opacity,--div--;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:tabs.access,starttime, endtime'],
    ],
    'palettes' => [
        '1' => ['showitem' => 'latitude, longitude, --linebreak--, latitude_orig, longitude_orig'],
        '2' => ['showitem' => 'latitude, longitude, radius, --linebreak--, latitude_orig, longitude_orig'],
    ],
];
