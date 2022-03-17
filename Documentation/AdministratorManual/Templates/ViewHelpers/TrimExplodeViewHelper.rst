.. include:: ../../../Includes.txt

=====================
TrimExplodeViewHelper
=====================

This is a ViewHelper to convert a comma separated value into an array.
All values will be trimmed.

**Type:** Basic

General properties
==================

.. t3-field-list-table::
 :header-rows: 1

 - :Name: Name:
   :Type: Type:
     :Description: Description:
     :Default value: Default value:

   - :Name:
           \* delimiter
   :Type:
           string
     :Description:
           Delimiter
     :Default value:
           ,

Examples
========

Basic example
-------------

.. code-block:: html

  <f:for each="{poiCollection.address -> maps2:trimExplode()}" as="address" iteration="iterator">
    ..do something with {address}
  </f:for>
