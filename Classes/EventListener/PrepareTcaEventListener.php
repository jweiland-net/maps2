<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\EventListener;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Configuration\MapProviderEnum;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\AfterTcaCompilationEvent;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

#[AsEventListener(
    identifier: 'maps2:prepareMaps2Relation',
)]
final readonly class PrepareTcaEventListener
{
    private const TABLE_POI = 'tx_maps2_domain_model_poicollection';

    private const TABLE_CATEGORY = 'sys_category';

    public function __construct(
        private ExtConf $extConf,
        private MapProviderEnum $mapProvider,
    ) {}

    public function __invoke(AfterTcaCompilationEvent $event): void
    {
        $tca = $event->getTca();

        $this->prepareMaps2Relation($tca);
        $this->setDefaultMapProvider($tca);
        $this->setDefaultMapType($tca);
        $this->updateFloatingValuesForPosition($tca);
        $this->activateMapProviderColumn();
        $this->addMaps2ColumnsToSysCategory();

        $event->setTca($tca);
    }

    private function prepareMaps2Relation(array &$tca): void
    {
        foreach ($tca as &$tableDefinition) {
            if (!isset($tableDefinition['columns'])) {
                continue;
            }
            if (!is_array($tableDefinition['columns'])) {
                continue;
            }
            foreach ($tableDefinition['columns'] as &$fieldConfig) {
                if (($fieldConfig['config']['type'] ?? '') !== 'group') {
                    continue;
                }

                if (($fieldConfig['config']['renderType'] ?? '') !== 'maps2Relation') {
                    continue;
                }

                if (!isset($fieldConfig['exclude'])) {
                    $fieldConfig['exclude'] = true;
                }

                if (!isset($fieldConfig['label'])) {
                    $fieldConfig['label'] = 'maps2.db:tx_maps2_uid';
                }

                $fieldConfig['config']['allowed'] = self::TABLE_POI;
                $fieldConfig['config']['default'] = 0;
                $fieldConfig['config']['foreign_table'] = self::TABLE_POI;
                $fieldConfig['config']['maxitems'] = 1;
                $fieldConfig['config']['minitems'] = 0;
                $fieldConfig['config']['prepend_tname'] = false;
                $fieldConfig['config']['size'] = 1;
                $fieldConfig['config']['suggestOptions'] = [
                    'default' => [
                        'searchWholePhrase' => true,
                    ],
                ];
            }
        }
    }

    private function setDefaultMapProvider(array &$tca): void
    {
        $tca[self::TABLE_POI]['columns']['map_provider']['config']['default'] = $this->mapProvider->value;
    }

    private function setDefaultMapType(array &$tca): void
    {
        $tca[self::TABLE_POI]['columns']['collection_type']['config']['default'] = $this->extConf->getDefaultMapType();
    }

    private function updateFloatingValuesForPosition(array &$tca): void
    {
        $tca[self::TABLE_POI]['columns']['latitude']['config']['default'] = number_format($this->extConf->getDefaultLatitude(), 6);
        $tca[self::TABLE_POI]['columns']['longitude']['config']['default'] = number_format($this->extConf->getDefaultLongitude(), 6);
    }

    private function activateMapProviderColumn(): void
    {
        if ($this->extConf->getMapProvider() === 'both') {
            ExtensionManagementUtility::addToAllTCAtypes(
                'tx_maps2_domain_model_poicollection',
                'map_provider',
                '',
                'before:configuration_map',
            );
        }
    }

    private function addMaps2ColumnsToSysCategory(): void
    {
        $newSysCategoryColumn = [
            'maps2_marker_icons' => [
                'exclude' => 1,
                'label' => 'maps2.db:sys_category.maps2_marker_icons.' . $this->mapProvider->value,
                'description' => 'maps2.db:sys_category.maps2_marker_icons.' . $this->mapProvider->value . '.description',
                'config' => [
                    'type' => 'file',
                    'minitems' => 0,
                    'maxitems' => 1,
                    'allowed' => 'common-image-types',
                ],
            ],
            'maps2_marker_icon_width' => [
                'exclude' => true,
                'label' => 'maps2.db:sys_category.maps2_marker_icon_width.' . $this->mapProvider->value,
                'description' => 'maps2.db:sys_category.maps2_marker_icon_width.' . $this->mapProvider->value . '.description',
                'config' => [
                    'type' => 'number',
                    'format' => 'integer',
                ],
            ],
            'maps2_marker_icon_height' => [
                'exclude' => true,
                'label' => 'maps2.db:sys_category.maps2_marker_icon_height.' . $this->mapProvider->value,
                'description' => 'maps2.db:sys_category.maps2_marker_icon_height.' . $this->mapProvider->value . '.description',
                'config' => [
                    'type' => 'number',
                    'format' => 'integer',
                ],
            ],
            'maps2_marker_icon_anchor_pos_x' => [
                'exclude' => true,
                'label' => 'maps2.db:sys_category.maps2_marker_icon_anchor_pos_x.' . $this->mapProvider->value,
                'description' => 'maps2.db:sys_category.maps2_marker_icon_anchor_pos_x.' . $this->mapProvider->value . '.description',
                'config' => [
                    'type' => 'number',
                    'format' => 'integer',
                ],
            ],
            'maps2_marker_icon_anchor_pos_y' => [
                'exclude' => true,
                'label' => 'maps2.db:sys_category.maps2_marker_icon_anchor_pos_y.' . $this->mapProvider->value,
                'description' => 'maps2.db:sys_category.maps2_marker_icon_anchor_pos_y.' . $this->mapProvider->value . '.description',
                'config' => [
                    'type' => 'number',
                    'format' => 'integer',
                ],
            ],
        ];

        ExtensionManagementUtility::addTCAcolumns(self::TABLE_CATEGORY, $newSysCategoryColumn);
        ExtensionManagementUtility::addToAllTCAtypes(
            self::TABLE_CATEGORY,
            '--div--;maps2.db:tab.maps2.' . $this->mapProvider->value . ', maps2_marker_icons, maps2_marker_icon_width, maps2_marker_icon_height, maps2_marker_icon_anchor_pos_x, maps2_marker_icon_anchor_pos_y',
        );
    }
}
