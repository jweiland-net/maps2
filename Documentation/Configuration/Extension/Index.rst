..  include:: /Includes.rst.txt


..  _extensionSettings:

==================
Extension Settings
==================

Some general settings for maps2 can be configured in `Admin Tools` -> `Settings`.

The settings are divided into several tabs and described here in detail:

Tab: Basic
==========

mapProvider
-----------

Default: both

Decide, if you want to use `Google Maps` or `OpenStreetMap` in your project.
If you're unsure you can keep `both` active, but in that case you and/or your
editor have the possibility to switch between Map Providers in PoiCollection
record.

..  important::

    If you keep `both` active you have multiple static extension templates
    available. You have to decide for one map provider in your TS-template
    record. And yes, you can't show maps of both map providers on the same page.

..  important::

    If you change from one Map Provider to another we remove the static
    extension template of the prior Map Provider from selection of your
    TS-template record.

defaultMapProvider
------------------

Default: `gm`

This setting is only relevant if you have chosen `both` at mapProvider. Select
a default map provider to be preselected for new poi collection records.

..  important::

    If you keep `both` active you have multiple static extension templates
    available. You have to decide for one map provider in your TS-template
    record. And yes, you can't show maps of both map providers on the same page.

defaultMapType
--------------

Default: `Empty`

By default an editor has to choose which type of poi collection record he wants
to create. As an administrator or integrator you have the possibility to reduce
the allowed record types. In that case it may make sense to set default map
type to another value. Further the editor saves one further click.

defaultCountry
--------------

Default: [empty]

If a Google Maps Geocode request will be requested with only a postal code, the
map provider will try to find that postal code somewhere all over the world.
If your website is only available for one specified country, you can enter
a country name to reduce the map position to given country. If you have POIs
all over the world you should keep that field empty.

defaultLatitude
--------------

Default: [empty]

By default the map in new poi collection records will be somewhere in the
atlantic at longitude:latitude 0:0. Please set default latitude to a well
known start position to start search from.

defaultLongitude
----------------

Default: [empty]

By default the map in new poi collection records will be somewhere in the
atlantic at longitude:latitude 0:0. Please set default longitude to a well
known start position to start search from.

defaultRadius
-------------

Default: 250

While creating new poi collection records of type `Radius` we set the default
radius to 250 meters. Change that value here, if you need another default value.

explicitAllowMapProviderRequests
--------------------------------

Default: false

If you use our maps2 extension your browser will send requests to Google Servers
to retrieve the map images. These requests contains the IP address of the
website visitors which is a user defined information in some countries.
User defined information which will be sent to third party servers needs to be
explicit allowed by the visitor. Enable this option, if you need this explicit
activation of Google Maps in Cookie.

explicitAllowMapProviderRequestsBySessionOnly
---------------------------------------------

Default: false

If you use our maps2 extension your browser will send requests to Google Servers
to retrieve the map images. These requests contains the IP address of the
website visitors which is a user defined information in some countries.
User defined information which will be sent to third party servers needs to be
explicit allowed by the visitor. Enable this option, if you need this explicit
activation of Google Maps for current browser session.

..  important::

    Firefox stores the browser SESSION on exit by default. So this feature will
    not work for Firefox browsers except you configure your firefox explicit to
    destroy session vars on close.

infoWindowContentTemplatePath
-----------------------------

Default: `EXT:maps2/Resources/Private/Templates/InfoWindowContent.html`

You can define your own default template for the info window content when
clicking on a marker. Further you can override this template path again with
TypoScript:

..  code-block:: typoscript

    settings.infoWindowContentTemplatePath = EXT:my_ext/Resources/Private/Extensions/Maps2/InfoWindowContent.html


Tab: Gm
=======

googleMapsLibrary
-----------------

Default: `https://maps.googleapis.com/maps/api/js?key=|&libraries=places`

This is the link to the current Google Maps JavaScript Api. It is configured as
**wrap** so that you can decide where the ApiKey has to be inserted.

..  important::

    This configuration is only for Google Maps which are used in list module of
    TYPO3 Backend.

..  important::

    Please keep **places** API information in link, as it is need for address
    search while PoiCollection record creation.

googleMapsGeocodeUri
--------------------

Default: `https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s`

When you're searching for an address while creating PoiCollection records maps2
starts a Geocode request to Google Maps Geocode API. If needed you can change
that URI here.

..  important::

    There are two %s placeholders in URI. We replace them with sprintf(), so,
    if you change that URI the new URI must have these two placeholders, too.

googleMapsJavaScriptApiKey
--------------------------

Default: [empty]

Since 2018 Google Maps needs API keys to get their services to work. So with
version 2.0.0 of maps2 you can and have to set an API key for JavaScript based
requests to Google to show the map in TYPO3 backend. Yes, this configuration is
for the backend only. To allow loading maps for frontend you should set the
same or another API key in TypoScript (see section `Configuration`).

You can register API keys here: `Google Console<http://console.developers.google.com>`_

googleMapsGeocodeApiKey
-----------------------

Default: [empty]

Since 2018 Google Maps needs API keys to get their services to work. So with
version 2.0.0 of maps2 you can and have to set an API key for Geocoding requests
to Google to allow searching for latitude/longitude by a given address in
TYPO3 backend and frontend (Plugin CityMap).

You can register API keys here: `Google Console<http://console.developers.google.com>`_

Tab: Osm
========

openStreetMapGeocodeUri
-----------------------

Default: `https://nominatim.openstreetmap.org/search/%s?format=json&addressdetails=1`

When you're searching for an address while creating PoiCollection records maps2
starts a Geocode request to Open Street Map Geocode API. If needed you can
change that URI here.

..  important::

    There is one %s placeholder in URI for address. We replace it with
    sprintf(), so, if you change that URI the new URI must have this
    placeholder, too.

Tab: Design
===========

strokeColor
-----------

Default: #FF0000

If you work with poi collection records of type `Area`, `Route` or `Radius`
maps2 will use this color for borders of the overlays.

strokeOpacity
-------------

Default: 0.8

If you work with poi collection records of type `Area`, `Route` or `Radius`
maps2 will use this opacity to let the underlying map data shine through.

strokeWeight
------------

Default: 2

If you work with poi collection records of type `Area`, `Route` or `Radius`
maps2 will use this width as border thickness for the overlays.

fillColor
---------

Default: #FF0000

If you work with poi collection records of type `Area` or `Radius` maps2 will
fill the overlay with this color.

fillOpacity
-----------

Default: 0.35

If you work with poi collection records of type `Area` or `Radius` maps2 will
use this opacity to let the underlying map data shine through.

markerIconWidth
---------------

Default: 25

Define a default width for Marker Icons in pixel. You can override this value
individually in Category and PoiCollection records.

markerIconHeight
----------------

Default: 40

Define a default height for Marker Icons in pixel. You can override this value
individually in Category and PoiCollection records.

markerIconAnchorPosX
--------------------

Default: 13

Which horizontal pixel on the image points the position on the Google Maps.
You can override this value individually in sys_category and PoiCollection
records.

markerIconAnchorPosY
--------------------

Default: 40

Which vertical pixel on the image points the position on the Google Maps.
You can override this value individually in sys_category and PoiCollection
records.
