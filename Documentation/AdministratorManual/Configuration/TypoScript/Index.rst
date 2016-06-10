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

  ==================== ===================== ============ ========
  Property             Title                 Sheet        Type
  ==================== ===================== ============ ========
  poiCollection_       Show poiCollection    General      integer
  categories_          Categories            General      string
  mapWidth_            Map width             General      string
  mapHeight_           Map height            General      string
  zoom_                Zoom                  Map Options  integer
  mapTypeId_           Map type              Map Options  string
  panControl_          Pan control           Map Options  boolean
  zoomControl_         Zoom control          Map Options  boolean
  mapTypeControl_      Map type control      Map Options  boolean
  scaleControl_        Scale control         Map Options  boolean
  streetViewControl_   Street view control   Map Options  boolean
  overviewMapControl_  Overview map control  Map Options  boolean
  ==================== ===================== ============ ========

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

.. _tsPanControl:

panControl
""""""""""

.. container:: table-row

   Property
         panControl
   Data type
         boolean
   Description
         Show a pan control.

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

overviewMapControl
""""""""""""""""""

.. container:: table-row

   Property
         overviewMapControl
   Data type
         boolean
   Description
         Show an overview map control.
