.. include:: ../../../../Includes.txt

==========================
Widget / EditPoiViewHelper
==========================

If you have your own extension you can use this widget to create a map
with one Marker which a user can move via drag 'n drop or click on the map.
The ViewHelper adds some hidden fields with the new position which you
can catch in your extension with GeneralUtility::_POST()

.. important::
   Please make sure you have inserted the static template **Maps2 (maps2)**
   in your TypoScript template.

**Type:** Basic

General properties
==================

property
--------

In the Widget template itself the property will be used for the property attribute of the m:form.hidden element.
Check your domain model where you have added a relation to PoiCollection. If the getter method name is
`getLocationPoi` you have to set property to `locationPoi`. But in most cases it will be `txMaps2Uid`.

Code: ::

   <m:form.hidden class="title-{data.uid}" property="{property}.title" value="{title}" />


poiCollection
-------------

Set the object of type PoiCollection to this property. We will use it and prepare the HTML form to change
latitude and longitude.

title
-----

Title is a mandatory field in PoiCollection object. Set it to the title of your current model, if you want or assign a
hard-coded title.

override
--------

As you know the plugin settings will override the TypoScript settings. With `override` you can override
these settings again here in the template. Do not use it too often, as it will be hard to debug.

Examples
========

Basic example
-------------

Code: ::

   <maps2:widget.editPoi property="txMaps2Uid"
                         poiCollection="{company.txMaps2Uid}"
                         title="{company.title}"
                         override="{settings: {mapWidth: '100%', mapHeight: '300'}}" />
