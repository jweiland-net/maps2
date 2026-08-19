<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/address.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

ExtensionManagementUtility::addTCAcolumns(
    'tx_address_domain_model_address',
    [
        'tx_maps2_uid' => [
            'config' => [
                'type' => 'group',
                'renderType' => 'maps2Relation',
                'addressColumns' => [
                    'address',
                    'house_number',
                    'zip',
                    'city',
                    'country',
                ],
                'countryColumn' => 'country',
                'synchronizeColumns' => [
                    0 => [
                        // Prefer the company name. If empty, fall back to the concatenated first and last name.
                        'foreignColumnName' => [
                            'type' => 'coalesce',
                            'columns' => [
                                'company',
                                [
                                    'type' => 'concat',
                                    'columns' => ['first_name', 'last_name'],
                                    'glue' => ' ',
                                ],
                            ],
                        ],
                        'poiCollectionColumnName' => 'title',
                    ],
                    1 => [
                        'foreignColumnName' => 'hidden',
                        'poiCollectionColumnName' => 'hidden',
                    ],
                ],
            ],
        ],
    ],
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tx_address_domain_model_address',
    '--div--;maps2.db:tab.maps2.gm,tx_maps2_uid',
);
