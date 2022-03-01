.. include:: ../../Includes.txt

.. _maps2Record:

============
Maps2 record
============

The maps2 record is the most important record in this extension.

Global fields
=============

.. t3-field-list-table::
 :header-rows: 1

 - :Field:        Field
   :Description:  Description
 - :Field:        Hide
   :Description:  Activate checkbox to hide that marker from map in frontend
 - :Field:        Language
   :Description:  For which language this marker should be rendered
 - :Field:        Type
   :Description:  There are currently four different types available. Choose one of Point, Radius, Area and Route
 - :Field:        Title
   :Description:  Give your POI a title
 - :Field:        Address
   :Description:  This field is required, but readonly. To fill it you have to search for an address in the field
                  at the map and press <Return>. If Google Maps or OpenStreetMap has found your address, we will
                  automatically fill that field with a formatted address
 - :Field:        Map
   :Description:  If you are connected with the internet you should see a Map with a Marker. With a click on the map
                  or drag 'n drop you can move the marker on the map. If you have moved the marker the fields
                  latitude and longitude will be updated.
                  If you're working with Google Maps please set JavaScript- and GeoCoding API-Keys in ExtensionManager
 - :Field:        Latitude
   :Description:  If you you know the exact latitude you can type it in here. Else you have to use the search field
                  to navigate to the right position
 - :Field:        Longitude
   :Description:  If you you know the exact longitude you can type it in here. Else you have to use the search field
                  to navigate to the right position
 - :Field:        Publish date
   :Description:  This is a TYPO3 field. Give it a date when your marker should be shown on the map.
 - :Field:        Expiration date
   :Description:  This is a TYPO3 field. Give it a date when your marker should be hidden on the map.
 - :Field:        Categories
   :Description:  This field works with the category system of TYPO3 (since TYPO3 6.0). You can assign markers to
                  categories. If these categories have a marker icon defined, your marker will automatically get
                  that marker icon assigned. A marker icon defined in your marker itself has always a higher priority.

Additional fields for Point
===========================

.. t3-field-list-table::
 :header-rows: 1

 - :Field:        Field
   :Description:  Description
 - :Field:        Info window content
   :Description:  Here you can define a text which will be displayed in a little popup, if you click on a marker in
                  frontend
 - :Field:        Image(s) for info window content
   :Description:  Here you can define some images which should be added to the little popup.
 - :Field:        Marker icon
   :Description:  If you want, you can replace maker icon with your own icon. We prefer to use a small image.
 - :Field:        Marker icon width
   :Description:  If you have defined a marker icon, you should give it a fixed width here
 - :Field:        Marker icon height
   :Description:  If you have defined a marker icon, you should give it a fixed height here
 - :Field:        Marker icon position X
   :Description:  In case of Google Maps the lower left corner will point to the exact position on the map. With this
                  value you can move your icon horizontal by the amount of pixels.
                  In case of OpenStreetMap the center of your icon will be used to point to the exact position on
                  the map. If you set this value, the upper left corner will be used to move your icon horizontally.
                  move that position from center of icon. It
 - :Field:        Marker icon position Y
   :Description:  In case of Google Maps the lower left corner will point to the exact position on the map. With this
                  value you can move your icon vertically by the amount of pixels.
                  In case of OpenStreetMap the center of your icon will be used to point to the exact position on
                  the map. If you set this value, the upper left corner will be used to move your icon vertically.


Additional fields for Area
==========================

.. t3-field-list-table::
 :header-rows: 1

 - :Field:        Field
   :Description:  Description
 - :Field:        Info window content
   :Description:  Here you can define a text which will be displayed in a little popup, if you click on a marker in
                  frontend
 - :Field:        Image(s) for info window content
   :Description:  Here you can define some images which should be added to the little popup.
 - :Field:        Stroke color
   :Description:  Set stroke color of the outer border. If not set we use value from Extensionmanager configuration.
 - :Field:        Stroke opacity
   :Description:  Sets the border opacity. If 1 you will not see the Map behind the border. If 0 you will not see the
                  Border of the overlay. We prefer to set that value a little bit higher than **fill opacity**. If
                  not set we use value from Extensionmanager configuration.
 - :Field:        Stroke weight
   :Description:  The width of the border in pixel. If not set we use value from Extensionmanager configuration.
 - :Field:        Fill color
   :Description:  In case of Area and Radius you can choose a color to fill your marker.
         If not set we get that value from Extensionmanager configuration.
 - :Field:        Fill opacity
   :Description:  Sets the fill opacity. If 1 you will not see the Map behind the overlay. If 0 you will not see the
                  the overlay. If not set we use value from Extensionmanager configuration.


Additional fields for Route
===========================

.. t3-field-list-table::
 :header-rows: 1

 - :Field:        Field
   :Description:  Description
 - :Field:        Info window content
   :Description:  Here you can define a text which will be displayed in a little popup, if you click on a marker in
                  frontend
 - :Field:        Image(s) for info window content
   :Description:  Here you can define some images which should be added to the little popup.
 - :Field:        Stroke color
   :Description:  Set stroke color of the outer border. If not set we use value from Extensionmanager configuration.
 - :Field:        Stroke opacity
   :Description:  Sets the border opacity. If 1 you will not see the Map behind the border. If 0 you will not see the
                  Border of the overlay. If not set we use value from Extensionmanager configuration.
 - :Field:        Stroke weight
   :Description:  The width of the border in pixel. If not set we use value from Extensionmanager configuration.


Additional fields for Radius
============================

.. t3-field-list-table::
 :header-rows: 1

 - :Field:        Field
   :Description:  Description
 - :Field:        Info window content
   :Description:  Here you can define a text which will be displayed in a little popup, if you click on a marker in
                  frontend
 - :Field:        Image(s) for info window content
   :Description:  Here you can define some images which should be added to the little popup.
 - :Field:        Radius
   :Description:  Type in the radius of your marker or resize the radius on the map to update that field.
 - :Field:        Stroke color
   :Description:  Set stroke color of the outer border. If not set we use value from Extensionmanager configuration.
 - :Field:        Stroke opacity
   :Description:  Sets the border opacity. If 1 you will not see the Map behind the border. If 0 you will not see the
                  Border of the overlay. We prefer to set that value a little bit higher than **fill opacity**. If
                  not set we use value from Extensionmanager configuration.
 - :Field:        Stroke weight
   :Description:  The width of the border in pixel. If not set we use value from Extensionmanager configuration.
 - :Field:        Fill color
   :Description:  In case of Area and Radius you can choose a color to fill your marker.
         If not set we get that value from Extensionmanager configuration.
 - :Field:        Fill opacity
   :Description:  Sets the fill opacity. If 1 you will not see the Map behind the overlay. If 0 you will not see the
                  the overlay. If not set we use value from Extensionmanager configuration.


Working with type "Point"
=========================

First of all search for an address.

To fine tune the position you can click somewhere on the map or drag'n drop the marker to your preferred location.

Working with type "Area"
========================

First of all search for an address.

With left click somewhere on the map you can create new points on the map.
You need at least 3 clicks to see the fill color of the area.
Use right click to remove a point.

Route
=====

First of all search for an address.

With left click somewhere on the map you can create new points on the map.
Use right click to remove a point.

Radius
======

First of all search for an address.

Click somewhere on the map or drag'n drop the center of the radius to move the marker around.
You can resize the radius via drag'n drop with one of the points at the border.

