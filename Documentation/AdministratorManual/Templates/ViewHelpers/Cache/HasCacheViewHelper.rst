.. include:: /Includes.rst.txt


==========================
Cache / HasCacheViewHelper
==========================

This is a ViewHelper to check, if a cache entry exists.

**Type:** Basic

General properties
==================

..  container:: ts-properties

    =============== ============== ============
    Property        Data type      Default
    =============== ============== ============
    prefix_         String         infoWindow
    poiCollection_  PoiCollection
    =============== ============== ============

..  _prefix:

prefix
------

If you want you can define a prefix for the generated CacheIdentifier. Leave this
value empty to use "infoWindow" as default value

..  _poiCollection:

poiCollection
-------------

You must assign the PoiCollection object to this ViewHelper. We extract some data from
PoiCollection to build a more unique CacheIdentifier which can differ Caches in
multilingual environment.

Examples
========

Basic example
-------------

..  code-block:: html

    <f:if condition="{maps2:cache.hasCache(poiCollection: poiCollection)}">
      <f:then>
      </f:then>
      <f:else>
      </f:else>
    </f:if>
