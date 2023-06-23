..  include:: /Includes.rst.txt


.. _routing:

=======
Routing
=======

Since TYPO3 9 you have the possibility to configure human readable
URLs with help of RouteEnhancers.
`EXT:maps2` does not have any detail view of a PoiCollection, so following
routing configuration is just for the internal AJAX call to retrieve the
info window content when clicking on a marker.

Example Configuration
=====================

..  code-block:: none

    routeEnhancers:
      Maps2Plugin:
        type: Extbase
        extension: Maps2
        plugin: Maps2
        routes:
          -
            routePath: '/poi/{method}'
            _controller: 'Ajax::process'
        defaultController: 'PoiCollection::show'
        aspects:
          method:
            type: StaticValueMapper
            map:
              renderInfoWindowContent: 'renderInfoWindowContent'
