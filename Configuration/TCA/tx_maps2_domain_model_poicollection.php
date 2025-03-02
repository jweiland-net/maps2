<?php

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use JWeiland\Maps2\Tca\Type\FloatType;

return [
    'ctrl' => [
        'title' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection',
        'label' => 'title',
        'label_alt' => 'address',
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
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
            'showitem' => '--palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.language_hidden;language_hidden, l10n_diffsource,
            collection_type, title',
        ],
        'Point' => [
            'showitem' => '--palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.language_hidden;language_hidden, l10n_diffsource,
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
            'showitem' => '--palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.language_hidden;language_hidden, l10n_diffsource,
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
            'showitem' => '--palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.language_hidden;language_hidden, l10n_diffsource,
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
            'showitem' => '--palette--;LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:palette.language_hidden;language_hidden, l10n_diffsource,
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
        'language_hidden' => ['showitem' => 'hidden, sys_language_uid, l10n_parent'],
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
                'type' => 'group',
                'allowed' => 'tx_maps2_domain_model_poicollection',
                'size' => 1,
                'maxitems' => 1,
                'minitems' => 0,
                'default' => 0,
                'suggestOptions' => [
                    'default' => [
                        'searchWholePhrase' => true,
                        'addWhere' => 'AND tx_maps2_domain_model_poicollection.sys_language_uid IN (0,-1)',
                    ],
                ],
            ],
        ],
        'l10n_diffsource' => [
            'config' => [
                'type' => 'passthrough',
                'default' => '',
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
                        'label' => '',
                        'value' => '',
                        'invertStateDisplay' => true,
                    ],
                ],
            ],
        ],
        'starttime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.starttime',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
                'default' => 0,
            ],
            'l10n_mode' => 'exclude',
            'l10n_display' => 'defaultAsReadonly',
        ],
        'endtime' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.endtime',
            'config' => [
                'type' => 'datetime',
                'format' => 'datetime',
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
                'required' => true,
                'default' => 'Empty',
                'items' => [
                    [
                        'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.empty',
                        'value' => 'Empty',
                    ],
                    [
                        'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.point',
                        'value' => 'Point',
                        'icon' => 'EXT:maps2/Resources/Public/Icons/TypeSelectPoint.png',
                    ],
                    [
                        'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.area',
                        'value' => 'Area',
                        'icon' => 'EXT:maps2/Resources/Public/Icons/TypeSelectArea.png',
                    ],
                    [
                        'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.route',
                        'value' => 'Route',
                        'icon' => 'EXT:maps2/Resources/Public/Icons/TypeSelectRoute.png',
                    ],
                    [
                        'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.collectionType.radius',
                        'value' => 'Radius',
                        'icon' => 'EXT:maps2/Resources/Public/Icons/TypeSelectRadius.png',
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
                'required' => true,
                'size' => 30,
                'max' => 255,
                'eval' => 'trim',
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
                'max' => 255,
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
                        'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.mapProvider.gm',
                        'value' => 'gm',
                    ],
                    [
                        'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.mapProvider.osm',
                        'value' => 'osm',
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
                'eval' => FloatType::class,
            ],
        ],
        'longitude' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.longitude',
            'config' => [
                'type' => 'input',
                'size' => 12,
                'eval' => FloatType::class,
            ],
        ],
        'radius' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.radius',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
            ],
        ],
        'stroke_color' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.stroke_color',
            'config' => [
                'type' => 'color',
                'placeholder' => '#FF0000',
            ],
        ],
        'stroke_opacity' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.stroke_opacity',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'max' => 5,
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
                'max' => 5,
                'placeholder' => '2',
                'eval' => 'trim',
            ],
        ],
        'fill_color' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.fill_color',
            'config' => [
                'type' => 'color',
                'placeholder' => '#FF0000',
            ],
        ],
        'fill_opacity' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.fill_opacity',
            'config' => [
                'type' => 'input',
                'size' => 5,
                'max' => 5,
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
            'config' => [
                'type' => 'file',
                'minitems' => 0,
                'maxitems' => 1,
                'allowed' => 'common-image-types',
            ],
        ],
        'marker_icons' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.marker_icons',
            'config' => [
                'type' => 'file',
                'minitems' => 0,
                'maxitems' => 1,
                'allowed' => 'common-image-types',
            ],
        ],
        'marker_icon_width' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.marker_icon_width',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
            ],
        ],
        'marker_icon_height' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.marker_icon_height',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
            ],
        ],
        'marker_icon_anchor_pos_x' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.marker_icon_anchor_pos_x',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
            ],
        ],
        'marker_icon_anchor_pos_y' => [
            'exclude' => true,
            'label' => 'LLL:EXT:maps2/Resources/Private/Language/locallang_db.xlf:tx_maps2_domain_model_poicollection.marker_icon_anchor_pos_y',
            'config' => [
                'type' => 'number',
                'format' => 'integer',
            ],
        ],
        'categories' => [
            'exclude' => true,
            'label' => 'LLL:EXT:core/Resources/Private/Language/locallang_tca.xlf:sys_category.categories',
            'config' => [
                'type' => 'category',
                'size' => 20,
                'default' => 0,
            ],
        ],
    ],
];
