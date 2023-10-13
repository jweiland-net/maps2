<?php

return [
    'ctrl' => [
        'title' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection',
        'label' => 'title',
        'label_alt' => 'address',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        // ToDo: remove "cruser_id" while removing TYPO3 11 compatibility
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY title',
        'type' => 'collection_type',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'languageField' => 'sys_language_uid',
        'transOrigPointerField' => 'l10n_parent',
        'transOrigDiffSourceField' => 'l10n_diffsource',
        'delete' => 'deleted',
        'typeicon_column' => 'collection_type',
        'typeicon_classes' => [
            'default' => 'ext-maps2-record-type-point',
            'Empty' => 'ext-maps2-record-type-point',
            'Point' => 'ext-maps2-record-type-point',
            'Area' => 'ext-maps2-record-type-area',
            'Route' => 'ext-maps2-record-type-route',
            'Radius' => 'ext-maps2-record-type-radius',
        ],
        'enablecolumns' => [
            'disabled' => 'hidden',
            'starttime' => 'starttime',
            'endtime' => 'endtime',
        ],
        'searchFields' => 'title, address',
    ],
    'types' => [
        'Empty' => [
            'showitem' => '--palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.language_hidden;language_hidden, l10n_parent, l10n_diffsource,
            collection_type, title',
        ],
        'Point' => [
            'showitem' => '--palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.language_hidden;language_hidden, l10n_parent, l10n_diffsource,
                collection_type, title,
                --div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.map, address, configuration_map,
                --palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.latitude_longitude;latitude_longitude,
                --div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.style, info_window_content, info_window_images, marker_icons,
                --palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.marker_icon_size;marker_icon_size,
                --palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.marker_icon_pos;marker_icon_pos,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access,
                --div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category, categories',
        ],
        'Area' => [
            'showitem' => '--palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.language_hidden;language_hidden, l10n_parent, l10n_diffsource,
                collection_type, title,
                --div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.map, address, configuration_map,
                --palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.latitude_longitude;latitude_longitude,
                --div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.style, info_window_content, info_window_images,
                --palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.stroke;stroke,
                --palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.fill;fill,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access,
                --div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category, categories',
        ],
        'Route' => [
            'showitem' => '--palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.language_hidden;language_hidden, l10n_parent, l10n_diffsource,
                collection_type, title,
                --div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.map, address, configuration_map,
                --palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.latitude_longitude;latitude_longitude,
                --div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.style, info_window_content, info_window_images,
                --palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.stroke;stroke,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access,
                --div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category, categories',
        ],
        'Radius' => [
            'showitem' => '--palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.language_hidden;language_hidden, l10n_parent, l10n_diffsource,
                collection_type, title,
                --div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.map, address, configuration_map,
                --palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.latitude_longitude;latitude_longitude_radius,
                --div--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.style, info_window_content, info_window_images,
                --palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.stroke;stroke,
                --palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.fill;fill,
                --div--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.tabs.access,
                --palette--;LLL:EXT:frontend/Resources/Private/Language/locallang_tca.xlf:pages.palettes.access;access,
                --div--;LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.tabs.category, categories',
        ],
    ],
    'palettes' => [
        'latitude_longitude' => ['showitem' => 'latitude, longitude'],
        'latitude_longitude_radius' => ['showitem' => 'latitude, longitude, radius'],
        'language_hidden' => ['showitem' => 'hidden, sys_language_uid'],
        'marker_icon_size' => ['showitem' => 'marker_icon_width, marker_icon_height'],
        'marker_icon_pos' => ['showitem' => 'marker_icon_anchor_pos_x, marker_icon_anchor_pos_y'],
        'stroke' => ['showitem' => 'stroke_color, stroke_opacity, stroke_weight'],
        'fill' => ['showitem' => 'fill_color, fill_opacity'],
        'access' => [
            'showitem' => 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel,endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel',
        ],
    ],
    'columns' => [
        'sys_language_uid' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.language',
            'config' => [
                'type' => 'language',
            ],
        ],
        'l10n_parent' => [
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.l18n_parent',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    ['', 0],
                ],
                'foreign_table' => 'tx_maps2_domain_model_poicollection',
                'foreign_table_where' => 'AND tx_maps2_domain_model_poicollection.pid=###CURRENT_PID### AND tx_maps2_domain_model_poicollection.sys_language_uid IN (-1,0)',
                'default' => 0,
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => '',
            ],
        ],
        't3ver_label' => [
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.versionLabel',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'max' => 255,
            ],
        ],
        'hidden' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.visible',
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        0 => '',
                        1 => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'input',
                // ToDo: migrate to "datetime" while removing TYPO3 11 compatibility
                // ToDo: Remove eval=int while removing TYPO3 11 compatibility
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'input',
                // ToDo: migrate to "datetime" while removing TYPO3 11 compatibility
                // ToDo: Remove eval=int while removing TYPO3 11 compatibility
                'renderType' => 'inputDateTime',
                'eval' => 'datetime,int',
                'default' => 0,
                'range' => [
                    'upper' => mktime(0, 0, 0, 1, 1, 2038),
                ],
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'collection_type' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'default' => 'Empty',
                // ToDo: migrate to "required => true" while removing TYPO3 11 compatibility
                'eval' => 'required',
                'items' => [
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.empty',
                        'Empty',
                    ],
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.point',
                        'Point',
                        'EXT:maps2/Resources/Public/Icons/TypeSelectPoint.png',
                    ],
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.area',
                        'Area',
                        'EXT:maps2/Resources/Public/Icons/TypeSelectArea.png',
                    ],
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.route',
                        'Route',
                        'EXT:maps2/Resources/Public/Icons/TypeSelectRoute.png',
                    ],
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.radius',
                        'Radius',
                        'EXT:maps2/Resources/Public/Icons/TypeSelectRadius.png',
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
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.title',
            'config' => [
                'type' => 'input',
                'size' => 30,
                // ToDo: migrate to "required => true" while removing TYPO3 11 compatibility
                'eval' => 'trim,required',
            ],
        ],
        'address' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.address',
            'description' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.address.description',
            'config' => [
                'type' => 'input',
                'renderType' => 'maps2ReadOnlyInputText',
                'size' => 48,
                'placeholder' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.address.useSearchField',
            ],
        ],
        'map_provider' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.mapProvider',
            'description' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.mapProvider.description',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'size' => 1,
                'items' => [
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.mapProvider.gm',
                        'gm',
                    ],
                    [
                        'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.mapProvider.osm',
                        'osm',
                    ],
                ],
            ],
        ],
        'configuration_map' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.configuration_map',
            'description' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.configuration_map.description',
            'config' => [
                'type' => 'user',
                'renderType' => 'maps2MapProvider',
            ],
        ],
        'latitude' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.latitude',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => \JWeiland\Maps2\Tca\Type\FloatType::class,
            ],
        ],
        'longitude' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.longitude',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => \JWeiland\Maps2\Tca\Type\FloatType::class,
            ],
        ],
        'radius' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.radius',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => 'trim,int',
            ],
        ],
        'pois' => [
            'exclude' => true,
            'config' => [
                'type' => 'passthrough',
                'foreign_table' => 'tx_maps2_domain_model_poi',
                'foreign_field' => 'poicollection',
            ],
        ],
        'stroke_color' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.stroke_color',
            'config' => [
                'type' => 'input',
                'size' => 7,
                'placeholder' => '#FF0000',
                'eval' => 'trim',
            ],
        ],
        'stroke_opacity' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.stroke_opacity',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'placeholder' => '0.8',
                'eval' => 'trim',
            ],
        ],
        'stroke_weight' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.stroke_weight',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'placeholder' => '2',
                'eval' => 'trim',
            ],
        ],
        'fill_color' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.fill_color',
            'config' => [
                'type' => 'input',
                'size' => 7,
                'placeholder' => '#FF0000',
                'eval' => 'trim',
            ],
        ],
        'fill_opacity' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.fill_opacity',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'placeholder' => '0.35',
                'eval' => 'trim',
            ],
        ],
        'info_window_content' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.info_window_content',
            'config' => [
                'type' => 'text',
                'cols' => 80,
                'rows' => 15,
                'softref' => 'typolink_tag,email[subst],url',
                'enableRichtext' => true,
                'fieldWizard' => [
                    'showInfoWindowContentAbove' => [
                        'renderType' => 'maps2InfoWindowContent',
                    ],
                ],
            ],
        ],
        'info_window_images' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.info_window_images',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'info_window_images',
                [
                    'minitems' => 0,
                    'maxitems' => 1,
                    'foreign_match_fields' => [
                        'fieldname' => 'info_window_images',
                        'tablenames' => 'tx_maps2_domain_model_poicollection',
                    ],
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
                    ],
                    'appearance' => [
                        'showPossibleLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true,
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette',
                            ],
                            \TYPO3\CMS\Core\Resource\AbstractFile::FILETYPE_IMAGE => [
                                'showitem' => '
                                --palette--;LLL:EXT:core/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                                --palette--;;filePalette',
                            ],
                        ],
                    ],
                ]
            ),
        ],
        'marker_icons' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.marker_icons',
            'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
                'marker_icons',
                [
                    'minitems' => 0,
                    'maxitems' => 1,
                    'foreign_match_fields' => [
                        'fieldname' => 'marker_icons',
                        'tablenames' => 'tx_maps2_domain_model_poicollection',
                    ],
                    'behaviour' => [
                        'allowLanguageSynchronization' => true,
                    ],
                    'appearance' => [
                        'showPossibleLocalizationRecords' => true,
                        'showAllLocalizationLink' => true,
                        'showSynchronizationLink' => true,
                    ],
                    'overrideChildTca' => [
                        'types' => [
                            '0' => [
                                'showitem' => '
                            --palette--;LLL:EXT:core/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette',
                            ],
                            \TYPO3\CMS\Core\Resource\AbstractFile::FILETYPE_IMAGE => [
                                'showitem' => '
                            --palette--;LLL:EXT:core/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
                            --palette--;;filePalette',
                            ],
                        ],
                    ],
                ]
            ),
        ],
        'marker_icon_width' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.marker_icon_width',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'default' => 0,
                'eval' => 'trim',
            ],
        ],
        'marker_icon_height' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.marker_icon_height',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'default' => 0,
                'eval' => 'trim',
            ],
        ],
        'marker_icon_anchor_pos_x' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.marker_icon_anchor_pos_x',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'default' => 0,
                'eval' => 'trim',
            ],
        ],
        'marker_icon_anchor_pos_y' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.marker_icon_anchor_pos_y',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'default' => 0,
                'eval' => 'trim',
            ],
        ],
        'categories' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectTree',
                'foreign_table' => 'sys_category',
                'foreign_table_where' => ' AND sys_category.sys_language_uid IN (-1, 0) ORDER BY sys_category.sorting',
                'MM' => 'sys_category_record_mm',
                'MM_match_fields' => [
                    'fieldname' => 'categories',
                    'tablenames' => 'tx_maps2_domain_model_poicollection',
                ],
                'MM_opposite_field' => 'items',
                'treeConfig' => [
                    'parentField' => 'parent',
                    'appearance' => [
                        'showHeader' => true,
                        'expandAll' => true,
                        'maxLevels' => 99,
                    ],
                ],
                'size' => 20,
                'maxitems' => 9999,
            ],
        ],
    ],
];
