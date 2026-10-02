..  include:: /Includes.rst.txt


..  _plugins:

========
Plugins
========

..  _plugins-show-map:

Maps2: Show map
===============

poiCollection
-------------

Default: [empty]

Define a poi collection record which should be shown on the website.

categories
----------

Default: [empty]

If you have not set a fixed poiCollection above you can choose one or more
categories here. If you have chosen more than one category some checkboxes will
appear below the map in frontend where you can switch the markers of the
chosen category on and off.

mapWidth
--------

Default: 100%

The width of the map.

mapHeight
---------

Default: 300

The height of the map.

zoom
----

Default: 12

Set default zoom for map in frontend.

forceZoom
---------

Default: false

This setting is only interesting, if you will show multiple POIs on map. In
that case maps2 will zoom out until all POIs can be displayed. This is realized
with the BoundingBox feature of Google Maps or OpenStreetMap. If you don't want
maps2 to zoom out, because you have POIs all around the world for example, you
can activate this checkbox to prevent automatic zooming.

zoomControl
----------

Default: true

Show zoom control of map provider.

activateScrollWheel
-------------------

Default: true

If deactivated you can not zoom via your mouse scroll wheel over the map.

fullScreenControl
-----------------

Default: true

Show control to toggle between normal and full screen mode.

..  _plugins-show-map-marker-clusterer:

markerClusterer.override
------------------------

Default: Use default (site settings / TypoScript)

Enable or disable marker clustering for this content element only. This option
applies to Google Maps and OpenStreetMap. With the default option the value of
site setting `maps2.enableMarkerClusterer` (TypoScript
`plugin.tx_maps2.settings.markerClusterer.enable`) is used.

The required JavaScript libraries are loaded automatically, if at least one map
on the page uses marker clustering. The cluster icons are configured with
TypoScript `markerClusterer.imagePath` (Google Maps) and
`markerClusterer.styleSheet` (OpenStreetMap).

mapTypeId
---------

Default: Road map
Map Provider: Google Maps only

Choose one of the four map views:

*   Hybrid
*   Road Map (default)
*   Satellite
*   Terrain

mapTypeControl
---------

Default: Road map
Map Provider: Google Maps only

Show a control to switch the map view

scaleControl
------------

Default: Road map
Map Provider: Google Maps only

Show a control to scale the map

streetViewControl
-----------------

Default: Road map
Map Provider: Google Maps only

Show a control to switch over to street view.

styles
------

Default: Road map
Map Provider: Google Maps only

Have a look at https://snazzymaps.com to copy some cool styles for you map.

mapTile
-------

Default: Road map
Map Provider: Open Street Map only

Define another external provider to show map with another layout

mapTileAttribution
------------------

Default: Road map
Map Provider: Open Street Map only

Each map needs to show the author attribution. Please check which Attribution
has to be used for defined map tile above.


..  _plugins-search-radius:

Maps2: Search Radius
====================

mapWidth
--------

Default: 100%

The width of the map.

mapHeight
---------

Default: 300

The height of the map.

zoom
----

Default: 12

Set default zoom for map in frontend.

forceZoom
---------

Default: false

This setting is only interesting, if you will show multiple POIs on map. In that
case maps2 will zoom out until all POIs can be displayed. This is realized with
the BoundingBox feature of Google Maps or OpenStreetMap. If you don't want maps2
to zoom out, because you have POIs all around the world for example, you can
activate this checkbox to prevent automatic zooming.

zoomControl
----------

Default: true

Show zoom control of map provider.

activateScrollWheel
-------------------

Default: true

If deactivated you can not zoom via your mouse scroll wheel over the map.

fullScreenControl
-----------------

Default: true

Show control to toggle between normal and full screen mode.

mapTypeId
---------

Default: Road map
Map Provider: Google Maps only

Choose one of the four map views:

*   Hybrid
*   Road Map (default)
*   Satellite
*   Terrain

mapTypeControl
---------

Default: Road map
Map Provider: Google Maps only

Show a control to switch the map view

scaleControl
------------

Default: Road map
Map Provider: Google Maps only

Show a control to scale the map

streetViewControl
-----------------

Default: Road map
Map Provider: Google Maps only

Show a control to switch over to street view.

styles
------

Default: Road map
Map Provider: Google Maps only

Have a look at https://snazzymaps.com to copy some cool styles for you map.

mapTile
-------

Default: Road map
Map Provider: Open Street Map only

Define another external provider to show map with another layout

mapTileAttribution
------------------

Default: Road map
Map Provider: Open Street Map only

Each map needs to show the author attribution. Please check which Attribution
has to be used for defined map tile above.


..  _plugins-city-map:

Maps2: City Map
===============

autoAppend
----------

Default: [empty]

Append a fixed string to search value. Maybe a cityname. That way the user
has only to insert a street.

mapWidth
--------

Default: 100%

The width of the map.

mapHeight
---------

Default: 300

The height of the map.

zoom
----

Default: 12

Set default zoom for map in frontend.

forceZoom
---------

Default: false

This setting is only interesting, if you will show multiple POIs on map. In
that case maps2 will zoom out until all POIs can be displayed. This is realized
with the BoundingBox feature of Google Maps or OpenStreetMap. If you don't want
maps2 to zoom out, because you have POIs all around the world for example, you
can activate this checkbox to prevent automatic zooming.

zoomControl
----------

Default: true

Show zoom control of map provider.

activateScrollWheel
-------------------

Default: true

If deactivated you can not zoom via your mouse scroll wheel over the map.

fullScreenControl
-----------------

Default: true

Show control to toggle between normal and full screen mode.

mapTypeId
---------

Default: Road map
Map Provider: Google Maps only

Choose one of the four map views:

*   Hybrid
*   Road Map (default)
*   Satellite
*   Terrain

mapTypeControl
---------

Default: Road map
Map Provider: Google Maps only

Show a control to switch the map view

scaleControl
------------

Default: Road map
Map Provider: Google Maps only

Show a control to scale the map

streetViewControl
-----------------

Default: Road map
Map Provider: Google Maps only

Show a control to switch over to street view.

styles
------

Default: Road map
Map Provider: Google Maps only

Have a look at https://snazzymaps.com to copy some cool styles for you map.

mapTile
-------

Default: Road map
Map Provider: Open Street Map only

Define another external provider to show map with another layout

mapTileAttribution
------------------

Default: Road map
Map Provider: Open Street Map only

Each map needs to show the author attribution. Please check which Attribution
has to be used for defined map tile above.
