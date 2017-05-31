.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../_IncludedDirectives.rst

.. _ts:

TypoScript
==========

This page is divided into the following sections which are all configurable by using TypoScript:

.. only:: html

   .. contents::
        :local:
        :depth: 1


Plugin settings
---------------
This section covers all settings, which can be defined in the plugin itself.

.. important:: Every setting can also be defined by TypoScript.

Properties
^^^^^^^^^^

.. container:: ts-properties

  =============================== =========================== ========== ========
  Property                        Title                       Sheet        Type
  =============================== =========================== ============ ========
  poiCollection_                  Show poiCollection          General      integer
  categories_                     Categories                  General      string
  mapWidth_                       Map width                   General      string
  mapHeight_                      Map height                  General      string
  zoom_                           Zoom                        Map Options  integer
  mapTypeId_                      Map type                    Map Options  string
  zoomControl_                    Zoom control                Map Options  boolean
  mapTypeControl_                 Map type control            Map Options  boolean
  scaleControl_                   Scale control               Map Options  boolean
  streetViewControl_              Street view control         Map Options  boolean
  fullScreenControl_              Full Screen control         Map Options  boolean
  activateScrollWheel_            Activate Scroll Wheel zoom  Map Options  boolean
  infoWindowContentTemplatePath_  Overview map control        Map Options  boolean
  =============================== =========================== ============ ========

.. _tsPoiCollection:

poiCollection
"""""""""""""

.. container:: table-row

   Property
         poiCollection
   Data type
         integer
   Description
         Define a poiCollection which should be shown on the website

.. _tsCategories:

categories
""""""""""

.. container:: table-row

   Property
         categories
   Data type
         string
   Description
         If you have not set a fixed poiCollection above you can choose one or more categories here.
         If you have chosen more than one category some checkboxes will appear below the map in frontend
         where you can switch the markers of the chosen category on and off.

.. _tsMapWidth:

mapWidth
""""""""

.. container:: table-row

   Property
         mapWidth
   Data type
         string
   Description
         The width of the map.

.. _tsMapHeight:

mapHeight
"""""""""

.. container:: table-row

   Property
         mapHeight
   Data type
         string
   Description
         The height of the map.

.. _tsZoom:

zoom
""""

.. container:: table-row

   Property
         zoom
   Data type
         integer
   Description
         A zoom value how deep to zoom in into the map.

.. _tsMapTypeId:

mapTypeId
"""""""""

.. container:: table-row

   Property
         mapTypeId
   Data type
         string
   Description
         Show Roadmap, Earthview or Streetview

.. _tsZoomControl:

zoomControl
"""""""""""

.. container:: table-row

   Property
         zoomControl
   Data type
         boolean
   Description
         Show a zoom control.

.. _tsMapTypeControl:

mapTypeControl
""""""""""""""

.. container:: table-row

   Property
         mapTypeControl
   Data type
         boolean
   Description
         Show a map type control.

.. _tsScaleControl:

scaleControl
""""""""""""

.. container:: table-row

   Property
         scaleControl
   Data type
         boolean
   Description
         Show a scale control.

.. _tsStreetViewControl:

streetViewControl
"""""""""""""""""

.. container:: table-row

   Property
         streetViewControl
   Data type
         boolean
   Description
         Show a street view control.

.. _tsOverviewMapControl:

fullScreenControl
"""""""""""""""""

.. container:: table-row

   Property
         fullScreenControl
   Data type
         boolean
   Description
         Toggle between normal and full screen mode.

.. _tsActivateScrollWheel:

activateScrollWheel
"""""""""""""""""""

.. container:: table-row

   Property
         activateScrollWheel
   Data type
         boolean
   Description
         If deactivated you can not zoom via your mouse scroll wheel.

infoWindowContentTemplatePath
"""""""""""""""""""""""""""""

.. container:: table-row

   Property
         infoWindowContentTemplatePath
   Data type
         string
   Description
         The info-window of a marker has its own html template, which you can set to your own destination. This
         setting it not part of the plugin configuration and can only be set within the settings-part in TS
         setup. File prefixes like EXT: are allowed.
         For extension developers: If you have build your own extension and you want to use the maps2 widgets, you
         have to provide this setting with the exact same name to your extension, too. If not, we will use the
         default template path of extension configuration in extension manager.
