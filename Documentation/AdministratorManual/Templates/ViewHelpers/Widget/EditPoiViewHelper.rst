.. include:: ../../../../Includes.rst.txt

Widget / EditPoiViewHelper
==========================

If you have your own extension you can use this widget to create a map
with one Marker which a user can move via drag 'n drop or click on the map.
The ViewHelper adds some hidden fields with the new position which you
can catch in your extension with GeneralUtility::_POST

.. important::
   Please make sure you have inserted the static template **Maps2 (maps2)**
   in your TypoScript template.

**Type:** Basic

General properties
------------------

.. t3-field-list-table::
 :header-rows: 1

 - :Name: Name:
   :Type: Type:
     :Description: Description:
     :Default value: Default value:

   - :Name:
           \* poiCollection
   :Type:
           integer|PoiCollection
     :Description:
           Which PoiCollection should be shown on Google Maps?
     :Default value:

Examples
--------

Basic example
~~~~~~~~~~~~~

Code: ::

  <maps2:widget.editPoi poiCollection="{location.txMaps2Uid}" override="{settings: {mapWidth: '100%', mapHeight: '300'}}" />
