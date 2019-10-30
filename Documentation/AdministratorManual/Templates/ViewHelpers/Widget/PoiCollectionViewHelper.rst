.. include:: ../../../../Includes.txt

Widget / PoiCollectionViewHelper
================================

If you have your own extension you can use this widget to show PoiCollections
in your own extension.

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

  <maps2:widget.poiCollection poiCollection="{location.txMaps2Uid}" override="{settings: {mapWidth: '100%', mapHeight: '300'}}" />
