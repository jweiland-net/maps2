..  include:: /Includes.rst.txt


==========================
Cache / SetCacheViewHelper
==========================

This is a ViewHelper to set a new cache entry.

General properties
==================

..  confval-menu::
    :name: confval-set-cache
    :display: table
    :required:
    :type:

..  confval:: prefix
    :name: set-cache-prefix
    :required: false
    :type: string

    If you want you can define a prefix for the generated CacheIdentifier. Leave
    this value empty to use "infoWindow" as default value

..  confval:: poiCollection
    :name: set-cache-poiCollection
    :required: true
    :type: PoiCollection

    You must assign the PoiCollection object to this ViewHelper. We extract some
    data from PoiCollection to build a more unique CacheIdentifier which can differ
    Caches in multilingual environment.

..  confval:: data
    :name: set-cache-data
    :required: true
    :type: string

    The data as string which has to be stored.

..  confval:: tags
    :name: set-cache-tags
    :required: false
    :type: array

    You can define some additional CacheEntryTags if you want. By default we add
    two additional Cache Tags named `infoWindowUid{PoiCollectionUid}` and
    `infoWindowPid{PoiCollectionPid}`

..  confval:: lifetime
    :name: set-cache-lifetime
    :required: false
    :type: int

    How long (in seconds) the CacheEntry should be available, before it will be
    re-generated? Keep this value empty to use the Default Value of Storage-Backend.
    0 for unlimited.


Examples
========

Basic example
-------------

..  code-block:: html

    {maps2:cache.setCache(poiCollection: poiCollection, data: '{content->f:format.raw()}')}
