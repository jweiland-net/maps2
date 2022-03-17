.. include:: ../../../../Includes.txt

==========================
Cache / SetCacheViewHelper
==========================

This is a ViewHelper to set a new cache entry.

**Type:** Basic

General properties
==================

.. container:: ts-properties

   =============== ============== ============
   Property        Data type      Default
   =============== ============== ============
   prefix_         String         infoWindow
   poiCollection_  PoiCollection
   data_           String
   tags_           Array          Empty Array
   lifetime_       Integer        NULL
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

.. _data:

data
----

The data as string which has to be stored.

.. _tags:

tags
----

You can define some additional CacheEntryTags if you want. By default we add two additional
Cache Tags named "infoWindowUid{PoiCollectionUid}" and "infoWindowPid{PoiCollectionPid}"

.. _lifetime:

lifetime
--------

How long (in seconds) the CacheEntry should be available, before it will be re-generated?
Keep this value empty to use the Default Value of Storage-Backend. 0 for unlimited.

Examples
========

Basic example
-------------

.. code-block:: html

  {maps2:cache.setCache(poiCollection: poiCollection, data: '{content->f:format.raw()}')}
