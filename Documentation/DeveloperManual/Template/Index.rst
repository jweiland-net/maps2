..  include:: /Includes.rst.txt


..  _developer-api:

========
Template
========

With EXT:maps2 version 10.0.0 we have remove PoiCollection- and
EditPoiCollection ViewHelper. This part will explain how to change your
template to use the new implementation.

PoiCollection
=============

Add static TS template `Maps2 Default (maps2)` and one of
the `Maps2 for Google Maps (maps2)` or `Maps2 for Open Street Map (maps2)`
templates to the page where your extension/plugin belongs to, as far as not
already done in root page.

In case of `Maps2 for Google Maps (maps2)`please check, if following
TS constant is set:

..  code-block:: typoscript

    plugin.tx_maps2.view.googleMapsJavaScriptApiKey

Add a further entry for `maps2` to `partialRootPaths` configuration of your
extension, so that fluid can find our new partials:

..  code-block:: typoscript

    plugin.tx_myext.view.partialRootPaths {
      0 = EXT:my_ext/Resources/Private/Partials/
      1 = EXT:maps2/Resources/Private/Partials/
    }

Replace old `maps2:widget.poiCollection` ViewHelper in templates of your
extension:

..  code-block:: html

    <maps2:widget.poiCollection poiCollection="{location.txMaps2Uid}" override="{settings: {mapWidth: '100%', mapHeight: '300', zoom: '14'}}" />

with following HTML:

..  code-block:: html

    <f:render partial="Maps2/PoiCollection"
              section="showMap"
              arguments="{poiCollection: location.txMaps2Uid, override: {settings: {mapWidth: '100%', mapHeight: '300', zoom: '14'}}}" />


EditPoiCollection
=================

Add static TS template `Maps2 Default (maps2)` and one of the
`Maps2 for Google Maps (maps2)` or `Maps2 for Open Street Map (maps2)` templates
to the page where your extension/plugin belongs to, as far as not already done
in root page.

In case of `Maps2 for Google Maps (maps2)`please check, if following
TS constant is set:

..  code-block:: typoscript

    plugin.tx_maps2.view.googleMapsJavaScriptApiKey

Add a further entry for `maps2` to `partialRootPaths` configuration of your
extension, so that fluid can find our new partials:

..  code-block:: typoscript

    plugin.tx_myext.view.partialRootPaths {
        0 = EXT:my_ext/Resources/Private/Partials/
        1 = EXT:maps2/Resources/Private/Partials/
    }

Replace old `maps2:widget.editPoiCollection` ViewHelper in templates of your
extension:

..  code-block:: html

    <maps2:widget.editPoi property="txMaps2Uid"
                          title="{company.company}"
                          poiCollection="{company.txMaps2Uid}"
                          override="{settings: {mapWidth: '100%', mapHeight: '300'}}" />

with following html:

..  code-block:: html

    <f:render partial="Maps2/EditPoiCollection"
              section="editMap"
              arguments="{poiCollection: company.txMaps2Uid, property: 'txMaps2Uid', title: company.company, override: {settings: {mapWidth: '100%', mapHeight: '300', zoom: '14'}}}" />
