..  include:: /Includes.rst.txt


..  _developer-maps2registry:

==============
Maps2 Registry
==============

Available since version 3.0.0

This is a pretty cool feature to extend your own extension with a new field
which will hold the reference UID to a PoiCollection record of maps2. So, if
you have a location record or something similar, then you can use our Maps2
registry to create a new field into a table of your extension. The default
name of the column will be ``tx_maps2_uid``, but you can change that, if you
want.

Our Maps2 registry is adapted from
`System categories API <https://docs.typo3.org/permalink/t3coreapi:categories-api>`_

Create a new file in [yourExt]/Configuration/TCA/Overrides/[yourTableName].php
and add the individually needed lines of code. Following is a slightly example
for events2 with all possible properties:

..  code-block:: php

    \JWeiland\Maps2\Tca\Maps2Registry::getInstance()->add(
        'events2', // Extension key of your extension
        'tx_events2_domain_model_location', // tablename of your location table
        [
            // add all columns to build a valid address as array
            // Add country only, if it is a string like "Germany". Else, see next options
            'addressColumns' => ['street', 'house_number', 'zip', 'city', 'country'],

            // You can define a hard-coded country for all addresses.
            'defaultCountry' => 'France',

            // Best option for country. If it is an INT and static_info_tables is loaded, it will
            // get country name from static_country.
            // If country could not be fetched, it will fallback to defaultCountry from above.
            'countryColumn' => 'country',

            // Optional: If you want to assign a PoiCollection only to a reduced set of records, you should use
            // ``columnMatch`` property. Internally this is a very simple array value comparison. No DB! If
            // you need more than simple comparison you can use SignalSlot in ``CreateMaps2RecordHook``.
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

            // With defaultStoragePid you can define where our maps2 record should be saved.
            // defaultStoragePid has following priority from low to high:
            // PID of your location record, Configuration of Maps2Registry, pageTSconfig (ext.maps2.defaultStoragePid)
            // This order is hardcoded and can not be changed.
            // So, if a PID with help of Maps2Registry was found, it will be overwritten with value of pageTSconfig.

            // Within the Maps2Registry we have following ordering from low to high:
            // A fixed value, an extension configuration value, a pageTSconfig value.

            // Define a fixed storage PID where to save our maps2 PoiCollection record.
            // Useful for small websites. Single domain instances.
            // Keep in mind that this value will be overwritten with pageTSconfig (ext.maps2.pid)
            'defaultStoragePid' => 414,

            // Do not configure "defaultStoragePid" if you want to save maps2 PoiCollection records
            // in same storage as your location record. But, be careful: As a fallback we are using
            // pageTSconfig path "ext.maps2.defaultStoragePid" which has a higher priority than PID of your location record.
            // So, keep that in mind, remove "ext.maps2.defaultStoragePid" from pageTSconfig to save maps2 record in same storage
            // of your records.

            // Read an extension manager configuration from ext_conf_template.txt of a given extension
            'defaultStoragePid' => [
                'extKey' => 'events2', // extension to read $EXTCONF from
                'property' => 'poiCollectionPid', // Property with storage UID
                'type' => 'extensionmanager' // If type is not given, we will use "extensionmanager" as default.
            ],

            // Read storage PID from pageTSconfig
            // You can configure that path to your needs. In example below we try to get storage PID
            // from pageTSconfig: ext.events2.poiCollectionPid = 4324
            // Do not forget: If pageTSconfig (ext.maps2.defaultStoragePid) is set, it will overwrite this configuration.
            'defaultStoragePid' => [
                'extKey' => 'events2', // Extension key to read storage PID from
                'property' => 'poiCollectionPid', // Property key to read storage PID from
                'type' => 'pagetsconfig'
            ],

            // Priority ordered version
            // We will read all these entries from array key 0 until array key 3. If a PID was found in f.e.
            // array key 2 (after entry 0 and 1 have not returned a valid PID) we will use it and will not
            // process further entries (entry 3)
            'defaultStoragePid' => [
                0 => [
                    'extKey' => 'news',
                    'property' => 'pid_of_maps2',
                    'type' => 'pagetsconfig'
                ],
                1 => [
                    'extKey' => 'my_ext',
                    'property' => 'specialConfiguredPidForMaps2',
                    'type' => 'pagetsconfig'
                ],
                2 => [
                    'extKey' => 'my_ext',
                    'property' => 'mapsPid',
                    'type' => 'extensionmanager'
                ],
                3 => [
                    'extKey' => 'events2',
                    'property' => 'poiCollectionPid',
                    'type' => 'extensionmanager'
                ],
            ],

            // You can synchronize additional fields of your record with maps2 PoiCollection
            // Please only use fields of type String or int.
            // 1:N, N:1 and N:M relations are not supported. Please use SignalSlot postUpdatePoiCollection
            // and synchronize them on your own.
            'synchronizeColumns' => [
                [
                    'foreignColumnName' => 'location', // column name of your extension
                    'poiCollectionColumnName' => 'title' // column name of maps2 PoiCollection record
                ]
            ]
        ]
    );

..  important::

    After adding these lines of code you have to de- and reactivate your
    extension in ExtensionManager to execute the SQL queries in behind.
    Alternatively you can go into InstallTool and execute Database Compare to
    insert the new configured field.


Example for tt_address
======================

..  code-block:: php

    <?php
    if (!defined('TYPO3')) {
        die('Access denied.');
    }

    call_user_func(function() {
        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('maps2')) {
            \JWeiland\Maps2\Tca\Maps2Registry::getInstance()->add(
                'tt_address',
                'tt_address',
                [
                    'addressColumns' => ['address', 'zip', 'city'],
                    'countryColumn' => 'country',
                    'synchronizeColumns' => [
                        [
                            'foreignColumnName' => 'name',
                            'poiCollectionColumnName' => 'title'
                        ]
                    ]
                ]
            );
        }
    });

..  _developer-maps2registry-synchronize-columns:

Synchronize Composed Values (Coalesce/Concat)
==============================================

You can add multiple configurations to `synchronizeColumns`. `foreignColumnName`
does not have to be a plain column name. It also accepts a structured
configuration to combine several columns of your foreign table into a single
value, which is resolved before it is written into the POI collection record.

..  _developer-maps2registry-synchronize-columns-coalesce:

Coalesce: Fall Back to Another Column
--------------------------------------

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

..  _developer-maps2registry-synchronize-columns-concat:

Concat: Combine Multiple Columns
---------------------------------

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
:ref:`developer-maps2registry-synchronize-columns-resolution-rules` below
for the exact rules governing empty and blank columns.

..  _developer-maps2registry-synchronize-columns-nesting:

Nesting Coalesce and Concat
----------------------------

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
:ref:`developer-maps2registry-synchronize-columns-resolution-rules` below.

..  _developer-maps2registry-synchronize-columns-shorthand:

Shorthand: Plain List as Coalesce
-----------------------------------

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

..  _developer-maps2registry-synchronize-columns-resolution-rules:

Value Resolution Rules
-----------------------

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
