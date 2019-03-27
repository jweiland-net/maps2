Cache / GetCacheViewHelper
--------------------------

This is a ViewHelper to retrieve a cache entry.

**Type:** Basic

General properties
^^^^^^^^^^^^^^^^^^

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

Examples
^^^^^^^^

Basic example
"""""""""""""

Code: ::

  {maps2:cache.getCache(cacheIdentifier: 'htmlCache{poiCollection.uid}')}
