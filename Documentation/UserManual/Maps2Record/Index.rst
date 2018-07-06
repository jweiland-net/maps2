.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../_IncludedDirectives.rst

.. _maps2Record:

Maps2 record
============

The maps2 record is the most important record in this extension.

.. t3-field-list-table::
 :header-rows: 1

 - :Field:
         Field
   :Description:
         Description
 - :Field:
         Collection type
   :Description:
         There are currently four different kinds of types available
         Choose one of Point, Radius, Area and Route
 - :Field:
         Title
   :Description:
         Give your marker a title:
 - :Field:
         Address
   :Description:
         Type in the address to search for in the map below.
         If Google Maps have found an address, it may be that your address
         will be a little bit reformatted.
 - :Field:
         Latitude
   :Description:
         If you you know the exact latitude you can type it in here.
         We prefer to generate the latitude over the address field and search.
 - :Field:
         Longitude
   :Description:
         If you you know the exact longitude you can type it in here.
         We prefer to generate the longitude over the address field and search.
 - :Field:
         Radius
   :Description:
         This field is only available for type ``Radius``.
         Type in the radius of your marker or resize the radius on the map to update that field.
 - :Field:
         Map
   :Description:
         If you are connected with the internet and have configured the Api Keys correctly
         you should see Google Maps here with a Marker.
         With a click on the map or drag'n drop you can move the marker around the map.
         If you have moved the marker the fields latitude and longitude will be updated.
 - :Field:
         Pois
   :Description:
         This is a not visible field. In case of **Route** and **Area**
         it will save all created markers on the map via Ajax in the background.
 - :Field:
         MarkerIconWidth
   :Description:
         Define a default width for Marker Icons in pixel.
 - :Field:
         MarkerIconWidth
   :Description:
         Define a default height for Marker Icons in pixel.
 - :Field:
         MarkerIconAnchorPosX
   :Description:
         Which horizontal pixel on the image points the position on the Google Maps.
 - :Field:
         MarkerIconAnchorPosX
   :Description:
         Which vertical pixel on the image points the position on the Google Maps.
 - :Field:
         Stroke color
   :Description:
         In case of Radius, Area and Route it will be the color of the outer border.
         If not set we get that value from Extensionmanager configuration.
 - :Field:
         Stroke opacity
   :Description:
         If 1 you will not see the Google Maps behind the border.
         If 0 you will not see the Border of the marker.
         We prefer to set that value a little bit higher than **fill opacity**.
         If not set we get that value from Extensionmanager configuration.
 - :Field:
         Stroke weight
   :Description:
         The width of the border
         If not set we get that value from Extensionmanager configuration.
 - :Field:
         Fill color
   :Description:
         In case of Area and Radius you can choose a color to fill your marker.
         If not set we get that value from Extensionmanager configuration.
 - :Field:
         Fill opacity
   :Description:
         If 1 you will not see the Google Maps behind the overlay.
         If 0 you will not see the overlay of the marker.
         We prefer to set that value a little bit lower than **stroke opacity**.
         If not set we get that value from Extensionmanager configuration.

Point
-----

Click somewhere on the map or drag'n drop the marker to your prefered location.

Radius
------

Click somewhere on the map or drag'n drop the center of the radius to move the marker around.
You can resize the radius via drag'n drop with one of the four point at the border.

Area
----

With left click somewhere on the map you can create new points on the map.
You need at least 3 clicks to see the fill color of the area.
Use right click to remove one point.

Route
-----

With left click somewhere on the map you can create new points on the map.
Use right click to remove one point.
