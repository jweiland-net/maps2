.. include:: ../../Includes.rst.txt

.. _developer-maps2registry:

Maps2 Registry
==============

Available since version 3.0.0

This is a pretty cool feature to extend your own extension with a new field which will
hold the reference UID to a PoiCollection record of maps2. So, if you have a location record or something
similar, then you can use our Maps2 registry to create a new field into a table of your extension. The default
name of the column will be ``tx_maps2_uid``, but you can change that, if you want.

Our Maps2 registry is adapted from :ref:`TYPO3s Category Registry <t3coreapi:system-categories-api>`

Create a new file in [yourExt]/Configuration/TCA/Overrides/[yourTableName].php
and add the individually needed lines of code. Following is a slightly example for events2 with all possible
properties:

.. code-block:: php

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

.. important::
    After adding these lines of code you have to de- and reactivate your extension in ExtensionManager to execute
    the SQL queries in behind. Alternatively you can go into InstallTool and execute Database Compare to insert
    the new configured field.


Example for tt_address
----------------------

.. code-block:: php

   <?php
   if (!defined('TYPO3_MODE')) {
       die('Access denied.');
   }

   call_user_func(function() {
       if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {
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
