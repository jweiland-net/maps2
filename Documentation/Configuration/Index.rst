.. include:: ../Includes.txt


.. _configuration:

=============
Configuration
=============

Target group: **Developers, Integrators**


Minimal Example
===============

.. hint::

   If you want to work with Google Maps in TYPO3 backend you or an administrator have to configure the
   Google Maps API keys in :ref:`Configure extension <extensionManager>` to get a working environment.

- Include static template `Maps2 Default (maps2)`
- include one of these static templates `Maps2 for Google Maps (maps2)` or
   `Maps2 for Open Street Map (maps2)`

Update these properties in TypoScript Constant Editor:

.. code-block:: none

   plugin.tx_maps2 {
       persistence {
           # We prefer to set a Storage PID where the maps2 records are located
           storagePid = 4
       }
       settings {
           # If you're using Google Maps you have to set an API key to allow loading the map in frontend
           googleMapsJavaScriptApiKey = ABC123...
       }
   }

.. _configuration-typoscript:

TypoScript Setup Reference
==========================

In this section we describe the TS properties of `plugin.tx_maps2.setting.*`

.. container:: ts-properties

   =============================== ========== ============================
   Property                        Data type  Default
   =============================== ========== ============================
   infoWindowContentTemplatePath_  String     EXT:maps2/...
   infoWindow_                     Array
   mapProvider_                    String     template related (gm or osm)
   markerClusterer_                Array      template related
   =============================== ========== ============================


.. _infoWindowContentTemplatePath:

infoWindowContentTemplatePath
-----------------------------

Example:
plugin.tx_maps2.settings.infoWindowContentTemplatePath = EXT:your_sitepackage/Resources/Templates/InfoWindowContent.html

Here you can define your own Fluid-Template for these little PopUps of Markers.

Since maps2 9.2.0 you have access to all related foreign records of your PoiCollection in Template.
Use: `<f:for each="{poiCollection.foreignRecords}" as="foreignRecord">...</f:for>`

As such a PoiCollection can be assigned to multiple different tables like tt_address, news, what ever, you can differ
between the foreign records with f.e.:

.. code-block:: html
   <f:groupedFor each="{poiCollection.foreignRecords}" as="groupedForeignRecords" groupBy="jwMaps2TableName" groupKey="tableName">
     <div>Table: {tableName}</div>
     <ul>
       <f:for each="{groupedForeignRecords}" as="foreignRecord">
         <li>PoiCollection URL: {foreignRecord.url}</li>
       </f:for>
     </ul>
   </f:groupedFor>

`jwMaps2TableName` and `jwMaps2ColumnName` are two special keys we have added to each foreign record.


.. _infoWindow:

infoWindow
----------

This property contains currently two properties to set the size of the images within infoWindow PopUp of Markers.

Example: plugin.tx_maps2.settings.infoWindow.image.width = 150c
Example: plugin.tx_maps2.settings.infoWindow.image.height = 150c

**image.width**

Set the maximum width of images within the InfoWindow PopUp

**image.height**

Set the maximum height of images within the InfoWindow PopUp


.. _mapProvider:

mapProvider
-----------

Normally you don't have to change that value, as it will be set automatically with the chosen static template. So,
if you use static template for OSM, this value will be set to `osm`. In case of the static template for Google Maps the
value will be set to `gm`.


.. _markerClusterer:

markerClusterer
---------------

This feature is only available for mapProvider `gm` (Google Maps).

If you have many POIs at a small range, it may help to activate markerClusterer to build little groups of POIs. That
way you will not see all thousands of POIs anymore, but maybe 5 groups, showing the amount of contained POIs. If you
zoom into the map, new groups, with a smaller collection will be build. If the collection reduces to 1 you will see
the original POI again.


Plugin settings
===============

This section covers all settings, which can be defined in the maps2 plugin and TypoScript.

.. important:: Every setting can also be defined by TypoScript: plugin.tx_maps2.settings.[propertyName]

.. container:: ts-properties

   ====================== =========================== ============ ========
   Property               Title                       Sheet        Type
   ====================== =========================== ============ ========
   poiCollection_         Show poiCollection          General      integer
   categories_            Categories                  General      string
   mapWidth_              Map width                   General      string
   mapHeight_             Map height                  General      string
   zoom_                  Zoom                        Map Options  integer
   forceZoom_             Force Zoom                  Map Options  boolean
   mapTypeId_             Map type                    Map Options  string
   zoomControl_           Zoom control                Map Options  boolean
   mapTypeControl_        Map type control            Map Options  boolean
   scaleControl_          Scale control               Map Options  boolean
   streetViewControl_     Street view control         Map Options  boolean
   fullScreenControl_     Full Screen control         Map Options  boolean
   activateScrollWheel_   Activate Scroll Wheel zoom  Map Options  boolean
   ====================== =========================== ============ ========

.. _poiCollection:

poiCollection
-------------

Define a poiCollection which should be shown on the website


.. _categories:

categories
----------

If you have not set a fixed poiCollection above you can choose one or more categories here. If you have chosen more
than one category some checkboxes will appear below the map in frontend where you can switch the markers of the
chosen category on and off.


.. _mapWidth:

mapWidth
--------

The width of the map.


.. _mapHeight:

mapHeight
---------

The height of the map.


.. _zoom:

zoom
----

A zoom value how deep to zoom in into the map.


.. _forceZoom:

forceZoom
---------

This setting is only interessting, if you will show multiple POIs on map. In that case maps2 will zoom out until
all POIs can be displayed. This is realized with the BoundingBox feature of Google Maps or OpenStreetMap. If
you don't want maps2 to zoom out, because you have POIs all around the world for example, you can activate
this checkbox to prevent automatic zooming.


.. _mapTypeId:

mapTypeId
---------

Show Roadmap, Earthview or Streetview


.. _zoomControl:

zoomControl
-----------

Show a zoom control.


.. _mapTypeControl:

mapTypeControl
--------------

Show a map type control.


.. _scaleControl:

scaleControl
------------

Show a scale control.


.. _streetViewControl:

streetViewControl
-----------------

Show a street view control.


.. _fullScreenControl:

fullScreenControl
-----------------

Toggle between normal and full screen mode.


.. _activateScrollWheel:

activateScrollWheel
-------------------

If deactivated you can not zoom via your mouse scroll wheel.
