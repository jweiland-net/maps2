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

   <?php
   \JWeiland\Maps2\Tca\Maps2Registry::getInstance()->add(
      'events2', // ext key
      'tx_events2_domain_model_location', // your tablename
      'maps2' // fieldname of table above. Defaults to `tx_maps2_uid`
   );

.. important::
    After adding these lines of code you have to de- and reactivate your extension in ExtensionManager to execute
    the SQL queries in behind. Alternatively you can go into InstallTool and execute Database Compare to insert
    the new configured field.