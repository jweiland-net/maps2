..  include:: /Includes.rst.txt


..  _developer-maps2-registry:

==============
Maps2 Registry
==============

This is a pretty cool feature to extend your own extension with a new field
which will hold the reference UID to a PoiCollection record of maps2. So, if
you have a location record or something similar, then you can use our Maps2
registry to create a new field into a table of your extension.

Create a new file in [yourExt]/Configuration/TCA/Overrides/[yourTableName].php
and add the needed lines of code. Keep an eye on the new `renderType`
`maps2Relation` which is available since maps2 13.0.0:

..  code-block:: php

    <?php

    use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

    ExtensionManagementUtility::addTCAcolumns(
        '[TABLE_NAME]',
        [
            '[COLUMN_NAME]' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => [],
                ],
            ],
        ],
    );

The maps2 registry will automatically be filled with every column identified
by this new renderType.

..  _developer-maps2-registry-basic-example:

Basic Example
=============

To help geocoding service you have to provide some address related columns
of your table. Would be good to enter the address columns in an official
order of the address form:

..  code-block:: php

    <?php

    use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

    ExtensionManagementUtility::addTCAcolumns(
        'tt_address',
        [
            'tx_maps2_uid' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => [
                        'address',
                        'zip',
                        'city',
                    ],
                ],
            ],
        ],
    );

    // Label with "Google Maps"
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        '--div--;maps2.db:tab.maps2.gm,tx_maps2_uid',
    );


..  _developer-maps2-registry-default-country:

Default Country
===============

To prevent geocoding service to search for your addresses all over the world
you should provide a default country:

..  code-block:: php

    <?php

    use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

    ExtensionManagementUtility::addTCAcolumns(
        'tt_address',
        [
            'tx_maps2_uid' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => [
                        'address',
                        'zip',
                        'city',
                    ],
                    'defaultCountry' => 'Germany',
                ],
            ],
        ],
    );

    // Label with "Google Maps"
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        '--div--;maps2.db:tab.maps2.gm,tx_maps2_uid',
    );


..  _developer-maps2-registry-country-column:

Dynamic country
===============

To prevent geocoding service to search for your addresses all over the world
you should provide a country. If you have a country column in your table
please provide it that way:

..  code-block:: php

    <?php

    use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

    ExtensionManagementUtility::addTCAcolumns(
        'tt_address',
        [
            'tx_maps2_uid' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => [
                        'address',
                        'zip',
                        'city',
                    ],
                    'countryColumn' => 'country',
                ],
            ],
        ],
    );

    // Label with "Google Maps"
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        '--div--;maps2.db:tab.maps2.gm,tx_maps2_uid',
    );

If you also add the country column to `addressColumns` it will automatically
removed internally.

Of cause you still can make use of `defaultCountry` as a fallback.


..  _developer-maps2-registry-matching columns:

Matching Columns
================

By default maps2 will try to assign a poi collection record to every
of your registered tables. But if you have a lot of records or you want to
reduce possible cost you may want to reduce the amount of records to a specific
storage record or another value of your records:

..  code-block:: php

    <?php

    use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

    ExtensionManagementUtility::addTCAcolumns(
        'tt_address',
        [
            'tx_maps2_uid' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => [
                        'address',
                        'zip',
                        'city',
                    ],
                    'columnMatch' => [
                        // Simple match
                        'pid' => '12',
                        'title' => 'jweiland.net',

                        // More complex examples:

                        // Same as above: equals
                        'pid' => [
                            'expr' => 'eq',
                            'value' => '12',
                        ]

                        // pid is in list of comma separated values
                        'pid' => [
                            'expr' => 'in',
                            'value' => '11,12,13',
                        ]

                        // pid is greater than 8
                        'pid' => [
                            'expr' => 'gt',
                            'value' => '8',
                        ]

                        // pid is greater than or equals 12
                        'pid' => [
                            'expr' => 'gte',
                            'value' => '12',
                        ]

                        // pid is less than 15
                        'pid' => [
                            'expr' => 'lt',
                            'value' => '15',
                        ]

                        // pid is less than or equals 12
                        'pid' => [
                            'expr' => 'lte',
                            'value' => '12',
                        ]
                    ],
                ],
            ],
        ],
    );

    // Label with "Google Maps"
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        '--div--;maps2.db:tab.maps2.gm,tx_maps2_uid',
    );


..  _developer-maps2-registry-simple-default-storage:

Simple Default Storage
======================

By default maps2 will store the related POI collection record on the same
storage folder as of your stored record. If you want maps2 to store
poi collection record in a different storage folder you can set a hard-coded
default storage PID:

..  code-block:: php

    <?php

    use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

    ExtensionManagementUtility::addTCAcolumns(
        'tt_address',
        [
            'tx_maps2_uid' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => [
                        'address',
                        'zip',
                        'city',
                    ],
                    'defaultStoragePid' => 4711,
                ],
            ],
        ],
    );

    // Label with "Google Maps"
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        '--div--;maps2.db:tab.maps2.gm,tx_maps2_uid',
    );

..  _developer-maps2-registry-default-storage-ext-conf:

Default Storage from Extension Configuration
============================================

By default maps2 will store the related POI collection record on the same
storage folder as of your stored record. If you want maps2 to store
poi collection record in a declared storage PID defined in on of the installed
extensions you can configure it that way:

..  code-block:: php

    <?php

    use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

    ExtensionManagementUtility::addTCAcolumns(
        'tt_address',
        [
            'tx_maps2_uid' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => [
                        'address',
                        'zip',
                        'city',
                    ],
                    'defaultStoragePid' => [
                        'type' => 'extensionmanager'
                        'extKey' => 'my_ext',
                        'property' => 'mapsPid',
                    ],
                ],
            ],
        ],
    );

    // Label with "Google Maps"
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        '--div--;maps2.db:tab.maps2.gm,tx_maps2_uid',
    );


..  _developer-maps2-registry-default-storage-pagetsconfig:

Default Storage from PageTS config
==================================

By default maps2 will store the related POI collection record on the same
storage folder as of your stored record. If you want maps2 to store
poi collection record in a declared storage PID defined by a value of
PageTS config `ext.my_ext.specialConfiguredPidForMaps2 = 4324`

..  code-block:: php

    <?php

    use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

    ExtensionManagementUtility::addTCAcolumns(
        'tt_address',
        [
            'tx_maps2_uid' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => [
                        'address',
                        'zip',
                        'city',
                    ],
                    'defaultStoragePid' => [
                        'type' => 'pagetsconfig'
                        'extKey' => 'my_ext',
                        'property' => 'specialConfiguredPidForMaps2',
                    ],
                ],
            ],
        ],
    );

    // Label with "Google Maps"
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        '--div--;maps2.db:tab.maps2.gm,tx_maps2_uid',
    );

..  _developer-maps2-registry-stacked-default-storage:

Stacked Default Storage
=======================

By default maps2 will store the related POI collection record on the same
storage folder as of your stored record. If you want maps2 to store
POI collection record in a different storage folder you can set various
ordered storage PID locations:

..  code-block:: php

    <?php

    use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

    ExtensionManagementUtility::addTCAcolumns(
        'tt_address',
        [
            'tx_maps2_uid' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => [
                        'address',
                        'zip',
                        'city',
                    ],
                    'defaultStoragePid' => [
                        0 => [
                            'type' => 'pagetsconfig'
                            'extKey' => 'news',
                            'property' => 'pid_of_maps2',
                        ],
                        1 => [
                            'type' => 'pagetsconfig'
                            'extKey' => 'my_ext',
                            'property' => 'specialConfiguredPidForMaps2',
                        ],
                        2 => [
                            'type' => 'extensionmanager'
                            'extKey' => 'my_ext',
                            'property' => 'mapsPid',
                        ],
                        3 => [
                            'type' => 'extensionmanager'
                            'extKey' => 'events2',
                            'property' => 'poiCollectionPid',
                        ],
                    ],
                ],
            ],
        ],
    );

    // Label with "Google Maps"
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        '--div--;maps2.db:tab.maps2.gm,tx_maps2_uid',
    );


..  _developer-maps2-registry-synchronize-columns:

Synchronize Columns
===================

By default maps2 will only add the related UID of POI collection to your record
but it is possible to synchronize further columns like a title od a hidden flag.
Following example shows how to synchronize location column of your table into
the title column of maps2 POI collection record:

..  code-block:: php

    <?php

    use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

    ExtensionManagementUtility::addTCAcolumns(
        'tt_address',
        [
            'tx_maps2_uid' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => [
                        'address',
                        'zip',
                        'city',
                    ],
                    'synchronizeColumns' => [
                        [
                            'foreignColumnName' => 'location',
                            'poiCollectionColumnName' => 'title'
                        ]
                    ]
                ],
            ],
        ],
    );

    // Label with "Google Maps"
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        '--div--;maps2.db:tab.maps2.gm,tx_maps2_uid',
    );

You can add multiple configuration to `synchronizeColumns`. BUT: Currently,
only 1:1 relations are allowed.


..  _developer-maps2-registry-override:

Override Configuration
======================

The Maps2Registry is an API, so other extension may make use of it and have
already created a relation for their tables. But you as a developer need a tool
to always override the maps2 registry to your needs.

That's why we have implemented an override feature:

..  code-block:: php

    <?php

    use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

    ExtensionManagementUtility::addTCAcolumns(
        'tt_address',
        [
            'tx_maps2_uid' => [
                'config' => [
                    'type' => 'group',
                    'renderType' => 'maps2Relation',
                    'addressColumns' => [
                        'address',
                        'zip',
                        'city',
                    ],
                    'override' => true,
                ],
            ],
        ],
    );

    // Label with "Google Maps"
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        '--div--;maps2.db:tab.maps2.gm,tx_maps2_uid',
    );

This will remove the registration of the other extensions/developers and just
yours will win.

..  _developer-maps2-registry-tab-openstreetmap:

Change maps2 tab to "OpenStreetMap"
===================================

In the examples above you only see how to create a new tab for
Google Maps to TCEforms in backend, but it is also possible to change that to
OpenStreetMap:

    // Label with "OpenStreetMap"
    ExtensionManagementUtility::addToAllTCAtypes(
        'tt_address',
        '--div--;maps2.db:tab.maps2.osm,tx_maps2_uid',
    );
