..  include:: /Includes.rst.txt


==========================
Cache / GetCacheViewHelper
==========================

This is a ViewHelper to retrieve a cache entry.

General properties
==================

..  confval-menu::
    :name: confval-get-cache
    :display: table
    :required:
    :type:

..  confval:: prefix
    :name: get-cache-prefix
    :required: false
    :type: string

    If you want you can define a prefix for the generated CacheIdentifier. Leave
    this value empty to use "infoWindow" as default value

..  confval:: poiCollection
    :name: get-cache-poiCollection
    :required: true
    :type: PoiCollection

    You must assign the PoiCollection object to this ViewHelper. We extract some
    data from PoiCollection to build a more unique CacheIdentifier which can differ
    Caches in multilingual environment.

Examples
========

Basic example
-------------

..  code-block:: html

    {maps2:cache.getCache(poiCollection: poiCollection)}

