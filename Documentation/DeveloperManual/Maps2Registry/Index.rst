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

You can add multiple configurations to `synchronizeColumns`. `foreignColumnName`
does not have to be a plain column name. It also accepts a structured
configuration to combine several columns of your foreign table into a single
value, which is resolved before it is written into the POI collection record.

..  _developer-maps2-registry-synchronize-columns-coalesce:

Coalesce: Fall Back to Another Column
-------------------------------------

Use `type` `coalesce` if you want maps2 to try several columns in order and
use the first one that is not empty. This is useful if your table stores
either a company name or a person name, for example:

..  code-block:: php
    :caption: EXT:my_ext/Configuration/TCA/Overrides/tt_address.php

    'synchronizeColumns' => [
        [
            'foreignColumnName' => [
                'type' => 'coalesce',
                'columns' => [
                    'company',
                    'name',
                ],
            ],
            'poiCollectionColumnName' => 'title',
        ],
    ],

..  _developer-maps2-registry-synchronize-columns-concat:

Concat: Combine Multiple Columns
--------------------------------

Use `type` `concat` if you want maps2 to join several columns into a single
value, separated by `glue` (defaults to a single space):

..  code-block:: php
    :caption: EXT:my_ext/Configuration/TCA/Overrides/tt_address.php

    'synchronizeColumns' => [
        [
            'foreignColumnName' => [
                'type' => 'concat',
                'columns' => [
                    'first_name',
                    'last_name',
                ],
                'glue' => ' ',
            ],
            'poiCollectionColumnName' => 'title',
        ],
    ],

Empty columns are skipped automatically, so a record with only a `last_name`
will not end up with a leading `glue` in the synchronized value. See
:ref:`developer-maps2-registry-synchronize-columns-resolution-rules` below
for the exact rules governing empty and blank columns.

..  _developer-maps2-registry-synchronize-columns-nesting:

Nesting Coalesce and Concat
---------------------------

Every entry of `columns` may again be a plain column name or a nested
`coalesce`/`concat` configuration. This allows you to combine both
strategies, for example: prefer the `company` column, otherwise fall back to
the concatenated `first_name` and `last_name` columns:

..  code-block:: php
    :caption: EXT:my_ext/Configuration/TCA/Overrides/tt_address.php

    'synchronizeColumns' => [
        [
            'foreignColumnName' => [
                'type' => 'coalesce',
                'columns' => [
                    'company',
                    [
                        'type' => 'concat',
                        'columns' => [
                            'first_name',
                            'last_name',
                        ],
                        'glue' => ' ',
                    ],
                ],
            ],
            'poiCollectionColumnName' => 'title',
        ],
    ],

Nesting works symmetrically and at any depth: a `concat` may just as well
contain a nested `coalesce` (e.g. `concat(first_name, coalesce(company,
last_name))`), and that combination may again be nested inside another
`coalesce` or `concat`. How an empty nested `concat` is treated by a
surrounding `coalesce` is described in
:ref:`developer-maps2-registry-synchronize-columns-resolution-rules` below.

..  _developer-maps2-registry-synchronize-columns-shorthand:

Shorthand: Plain List as Coalesce
---------------------------------

A plain, non-associative list of column names and/or nested configurations
(i.e. without `type`/`columns`) is automatically treated as a `coalesce`:
maps2 tries every entry in order and uses the first one that resolves to a
non-empty value. This is handy to fall back from one combined strategy to
another one, for example: prefer `company` or `name`, otherwise fall back to
the concatenated `first_name` and `last_name` columns:

..  code-block:: php
    :caption: EXT:my_ext/Configuration/TCA/Overrides/tt_address.php

    'synchronizeColumns' => [
        [
            'foreignColumnName' => [
                [
                    'type' => 'coalesce',
                    'columns' => ['company', 'name'],
                ],
                [
                    'type' => 'concat',
                    'columns' => ['first_name', 'last_name'],
                ],
            ],
            'poiCollectionColumnName' => 'title',
        ],
    ],

`type` also accepts a :php:`\JWeiland\Maps2\Tca\ForeignColumnResolveTypeEnum`
case directly instead of its string value, if you prefer a typed constant
over a magic string.

..  _developer-maps2-registry-synchronize-columns-resolution-rules:

Value Resolution Rules
----------------------

The following rules apply consistently to every example above, no matter
how deeply `coalesce` and `concat` are nested:

#.  Every column value is trimmed before it is evaluated. A column that
    only contains whitespace (e.g. `"   "` or a tab) is treated exactly
    like an empty column.

#.  `concat` filters out empty columns *before* joining them with `glue`,
    it does not join first and clean up afterwards. An empty column can
    therefore never produce a stray `glue` in the result, neither at the
    start, the end, nor in the middle. If *every* column of a `concat` is
    empty, the whole `concat` resolves to an empty string regardless of
    `glue`: a `, ` glue with all columns empty results in `''`, not in
    `", , ,"`.

#.  `coalesce` only checks whether a candidate resolved to a non-empty
    string. A nested `concat` that resolves to an empty string (per rule 2)
    is therefore treated like any other empty candidate: `coalesce` simply
    moves on to the next entry of `columns`.

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
