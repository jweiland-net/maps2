.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

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
and add following lines:

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
            // get country name from static_country
            // If country could not be fetched, it will fallback to defaultCountry from above
            'countryColumn' => 'country',

            // Remove defaultStoragePid, if you want to save maps2 PoiCollection in same Storage as your record
            // Define fixed storage PID where to save our maps2 PoiCollection record
            'defaultStoragePid' => 414,

            // Read an extension manager configuration from ext_conf_template.txt of an given extension
            'defaultStoragePid' => [
                'extKey' => 'events2', // extension to read $EXTCONF from
                'property' => 'poiCollectionPid' // Property with storage UID
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
