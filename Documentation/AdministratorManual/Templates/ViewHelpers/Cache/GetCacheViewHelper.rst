.. include:: ../../../../Includes.txt

==========================
Cache / GetCacheViewHelper
==========================

This is a ViewHelper to retrieve a cache entry.

**Type:** Basic

General properties
==================

.. container:: ts-properties

   =============== ============== ============
   Property        Data type      Default
   =============== ============== ============
   prefix_         String         infoWindow
   poiCollection_  PoiCollection
   =============== ============== ============

.. _prefix:

prefix
------

If you want you can define a prefix for the generated CacheIdentifier. Leave this
value empty to use "infoWindow" as default value

.. _poiCollection:

poiCollection
-------------

You must assign the PoiCollection object to this ViewHelper. We extract some data from
PoiCollection to build a more unique CacheIdentifier which can differ Caches in
multilingual environment.

Examples
========

Basic example
-------------

Code: ::

  {maps2:cache.getCache(poiCollection: poiCollection)}
