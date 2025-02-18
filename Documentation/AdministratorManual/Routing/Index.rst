..  include:: /Includes.rst.txt


.. _routing:

=======
Routing
=======

`EXT:maps2` does not have a list and detail view, so there is no need to
configure any route enhancers. But, it is possible to link to a POI
from foreign extensions. That is possible by defining the PoiCollection
UID as GET parameter `tx_maps2_maps2[poiCollectionUid]` in URI.

For this case you can use following configuration.

..  hint::

    As PoiCollection records do NOT have any slug column defined, we really
    prefer to use just the UID of the record. Please prevent the usage of
    any title column as that may lead to unexpected escaping problems in URI.
    If you really want to use a title please create a slug column on your own
    and reference that column in aspect yourself.

Example Configuration
=====================

..  code-block:: yaml

    routeEnhancers:
      Maps2Plugin:
        type: Extbase
        extension: Maps2
        plugin: Maps2
        routes:
          -
            routePath: '/poi/{poiCollectionUid}'
            _controller: 'PoiCollection::show'
        defaultController: 'PoiCollection::show'
        aspects:
          poiCollectionUid:
            type: PersistedAliasMapper
            tableName: 'tx_maps2_domain_model_poicollection'
            routeFieldName: 'uid'
