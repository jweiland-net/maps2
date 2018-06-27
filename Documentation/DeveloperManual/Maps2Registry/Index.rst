.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../_IncludedDirectives.rst

.. _developer-maps2registry:

Maps2Registry
=============

Maps2 3.0.0 comes with a new registry to add a maps2 relation to
your extensions. It works the same way like TYPO3s CategoryRegistry.

Create a new file in [yourExt]/Configuration/TCA/Overrides/[yourTableName].php
and add following lines

.. code-block:: php

   <?php
   // Update the category registry
   $result = \JWeiland\Maps2\Tca\Maps2Registry::getInstance()->add(
      'events2', // ext key
      'tx_events2_domain_model_location', // your tablename
      'maps2' // fieldname of table above. Defaults to tx_maps2_uid
   );
