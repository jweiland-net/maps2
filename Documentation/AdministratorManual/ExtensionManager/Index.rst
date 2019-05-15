.. include:: ../../Includes.rst.txt

.. _extensionManager:

Extension Manager
=================

Some general settings can be configured in the Extension Manager.
If you need to configure those, switch to the module "Extension Manager", select the extension "**maps2**" and press on the configure-icon!

.. figure:: /Images/AdministratorManual/maps2-configure-ExtensionManager.png
   :width: 500px
   :alt: Where is the button to start configuring maps2 in Extensionmanager

The settings are divided into several tabs and described here in detail:

.. container:: ts-properties

   ========================================== ======== ====================================================================
   Property                                   Tab      Default
   ========================================== ======== ====================================================================
   mapProvider_                               basic    both
   defaultMapProvider_                        basic    gm
   defaultCountry_                            basic
   defaultLatitude_                           basic    0.000000
   defaultLongitude_                          basic    0.000000
   defaultRadius_                             basic    250
   explicitAllowMapProviderRequests_          basic    0
   explicitAllowMapProviderRequestsBySession_ basic    0
   infoWindowContentTemplatePath_             basic    EXT:maps2/Resources/Private/Templates/InfoWindowContent.html
   allowMapTemplatePath_                      basic    EXT:maps2/Resources/Private/Templates/AllowMapForm.html

   googleMapsLibrary_                         gm       ``https://maps.googleapis.com/maps/api/js?key=|&libraries=places``
   googleMapsGeocodeUri_                      gm       ``https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s``
   googleMapsJavaScriptApiKey_                gm
   googleMapsGeocodeApiKey_                   gm

   openStreetMap_                             osm      ``https://nominatim.openstreetmap.org/search/%s?format=json&addressdetails=1``

   strokeColor_                               design   #FF0000
   strokeOpacity_                             design   0.8
   strokeWeight_                              design   2
   fillColor_                                 design   #FF0000
   fillOpacity_                               design   0.3
   markerIconWidth_                           design   25
   markerIconHeight_                          design   40
   markerIconAnchorPosX_                      design   13
   markerIconAnchorPosY_                      design   40
   ========================================== ======== ====================================================================


.. _extensionManager-mapProvider:

mapProvider
-----------

Decide, if you want to use Google Maps or OpenStreetMap in your project. If you're unsure you can keep both
active, but in that case you and/or your editor have the possibility to switch between Map Providers in
PoiCollection record.

.. important::
   If you keep both active you have multiple static extension templates available. You have to decide for one map
   provider in your TS-template record. And yes, you can't show maps of both map providers on the same page.

.. important::
   If you change from one Map Provider to another we remove the static extension template of the prior Map
   Provider from selection of your TS-template record.


.. _extensionManager-defaultMapProvider:

defaultMapProvider
------------------

This setting is only relevant if you have chosen ``both`` at mapProvider. c

.. important::
   If you keep both active you have multiple static extension templates available. You have to decide for one map
   provider in your TS-template record. And yes, you can't show maps of both map providers on the same page.


.. _extensionManager-defaultCountry:

defaultCountry
--------------

If a Google Maps Geocode Request will be requested with only a postal code,
Google Maps will try to find that postal code somewhere all over the world.
If your website is only available for one specified country, you can enter
a country name to reduce the Google Maps Position to given country. If you have
POIs all over the world you should keep that field empty.


.. _extensionManager-defaultLatitude:

defaultLatitude
---------------

Default latitude


.. _extensionManager-defaultLongitude:

defaultLongitude
----------------

Default longitude


.. _extensionManager-defaultRadius:

defaultRadius
-------------

Default radius


.. _extensionManager-explicitAllowMapProviderRequests:

explicitAllowMapProviderRequests
--------------------------------

If you use our maps2 extension your browser will send requests to Google Servers to retrieve the map images.
These requests contains the IP address of the website visitors which is a user defined information in some countries.
User defined information which will be sent to third party servers needs to be explicit allowed by the visitor.
Enable this option, if you need this explicit activation of Google Maps in Cookie.


.. _extensionManager-explicitAllowMapProviderRequestsBySession:

explicitAllowMapProviderRequestsBySession
-----------------------------------------

If you use our maps2 extension your browser will send requests to Google Servers to retrieve the map images.
These requests contains the IP address of the website visitors which is a user defined information in some countries.
User defined information which will be sent to third party servers needs to be explicit allowed by the visitor.
Enable this option, if you need this explicit activation of Google Maps for current browser session.

.. important::
   Firefox stores the browser SESSION on exit by default. So this feature will not work for Firefox browsers except you
   configure your firefox explicit to destroy session vars on close.


.. _extensionManager-infoWindowContentTemplatePath::

infoWindowContentTemplatePath
-----------------------------

You can define your own default template for these little info window content when clicking on a marker.
Further you can override this template path again with TypoScript at settings.infoWindowContentTemplatePath = [path]


.. _extensionManager-allowMapTemplatePath::

allowMapTemplatePath
--------------------

This option is only valid if explicitAllowMapProviderRequests is activ.
Define your own template which will be shown, as long as the visitor has not explicit allowed requests
to Google Servers
You can override this template path again with TypoScript at settings.allowMapTemplatePath = [path]


.. _extensionManager-googleMapsLibrary:

googleMapsLibrary
-----------------

This is the link to the current Google Maps JavaScript Api. It is configured as **wrap** so that you
can decide where the ApiKey has to be inserted.

.. important::
   This configuration is only for Google Maps which are used in list module of TYPO3 Backend.

.. important::
   Please keep **places** API information in link, as it is need for address search while PoiCollection
   record creation.


.. _extensionManager-googleMapsGeocodeUri:

googleMapsGeocodeUri
--------------------

When you're searching for an address while creating PoiCollection records maps2 starts a Geocode request to
Google Maps Geocode API. If needed you can change that URI here.

.. important::
   There are two %s placeholders in URI. We replace them with sprintf(), so, if you change that URI the new URI
   must have these two placeholders, too.


.. _extensionManager-googleMapsJavaScriptApiKey:

googleMapsJavaScriptApiKey
--------------------------

Since version 2.0.0 this extension needs a Google Maps JavaScript ApiKey which you have to get
over `Google Console<http://console.developers.google.com>`_


.. _extensionManager-googleMapsGeocodeApiKey:

googleMapsGeocodeApiKey
-----------------------

Since version 2.0.0 this extension needs a Google Maps Geocode ApiKey, if you use the CityMap plugin.
It was needed to get Latitude and Longitude from a given address.

Please visit `Google Console <http://console.developers.google.com>`_ to get one.


.. _extensionManager-openStreetMapGeocodeUri:

openStreetMapGeocodeUri
-----------------------

When you're searching for an address while creating PoiCollection records maps2 starts a Geocode request to
Open Street Map Geocode API. If needed you can change that URI here.

.. important::
   There is one %s placeholder in URI for address. We replace it with sprintf(), so, if you change that URI the new URI
   must have this placeholder, too.


.. _extensionManager-strokeColor::

strokeColor
-----------

Stroke color


.. _extensionManager-strokeOpacity:

strokeOpacity
-------------

Stroke opacity


.. _extensionManager-strokeWeight:

strokeWeight
------------

Stroke weight


.. _extensionManager-fillColor:

fillColor
---------

Fill color


.. _extensionManager-fillOpacity:

fillOpacity
-----------

Fill opacity


.. _extensionManager-markerIconWidth:

markerIconWidth
---------------

Define a default width for Marker Icons in pixel. You can override this value
individually in sys_category and PoiCollection records.


.. _extensionManager-markerIconHeight:

markerIconHeight
----------------

Define a default height for Marker Icons in pixel. You can override this value
individually in sys_category and PoiCollection records.


.. _extensionManager-markerIconAnchorPosX:

markerIconAnchorPosX
--------------------

Which horizontal pixel on the image points the position on the Google Maps.
You can override this value individually in sys_category and PoiCollection records.


.. _extensionManager-markerIconAnchorPosY:

markerIconAnchorPosY
--------------------

Which vertical pixel on the image points the position on the Google Maps.
You can override this value individually in sys_category and PoiCollection records.
