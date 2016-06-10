Cache / HasCacheViewHelper
--------------------------

This is a ViewHelper to check, if a cache entry exists.

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

  <f:if condition="{maps2:cache.hasCache(cacheIdentifier: 'htmlCache{poiCollection.uid}')}">
    <f:then>
    </f:then>
    <f:else>
    </f:else>
  </f:if>

