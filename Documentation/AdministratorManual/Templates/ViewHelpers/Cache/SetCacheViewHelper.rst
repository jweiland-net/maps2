.. include:: ../../../../Includes.rst.txt

Cache / SetCacheViewHelper
==========================

This is a ViewHelper to set a new cache entry.

**Type:** Basic

General properties
------------------

.. t3-field-list-table::
 :header-rows: 1

 - :Name: Name:
   :Type: Type:
   :Description: Description:
   :Default value: Default value:

 - :Name:
         \* cacheIdentifier
 :Type:
         string
   :Description:
         Cache identifier
   :Default value:

   - :Name:
           \* data
 :Type:
           array
     :Description:
           Data
     :Default value:

   - :Name:
           \* tags
 :Type:
           array
     :Description:
           Tags
     :Default value:

   - :Name:
           \* livetime
 :Type:
           integer
     :Description:
           Lifetime
     :Default value:

Examples
--------

Basic example
~~~~~~~~~~~~~

Code: ::

  {maps2:cache.setCache(cacheIdentifier: 'htmlCache{poiCollection.uid}', data: '{content->f:format.raw()}', tags: {0: poiCollection.pid}, lifetime: 3600)}
