.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../../_IncludedDirectives.rst

.. _extensionManager:

Extension Manager
-----------------

Some general settings can be configured in the Extension Manager.
If you need to configure those, switch to the module "Extension Manager", select the extension "**maps2**" and press on the configure-icon!

.. figure:: /Images/AdministratorManual/maps2-configure-ExtensionManager.png
   :width: 500px
   :alt: Where is the button to start configuring maps2 in Extensionmanager

The settings are divided into several tabs and described here in detail:

Properties
^^^^^^^^^^

.. container:: ts-properties

  ============================== ======== ====================================================================
  Property                       Tab      Default
  ============================== ======== ====================================================================
  googleMapsLibrary_              basic    ``https://maps.googleapis.com/maps/api/js?key=|&callback=initMap``
  useHttps_                       basic    0
  googleMapsJavaScriptApiKey_     basic
  googleMapsGeocodeApiKey_        basic
  defaultLongitude_               basic    0.000000
  defaultLatitude_                basic    0.000000
  defaultRadius_                  basic    250
  infoWindowContentTemplatePath_
  strokeColor_                    design   #FF0000
  strokeOpacity_                  design   0.8
  strokeWeight_                   design   2
  fillColor_                      design   #FF0000
  fillOpacity_                    design   0.3
  ============================== ======== ====================================================================

Property details
^^^^^^^^^^^^^^^^

.. only:: html

   .. contents::
        :local:
        :depth: 1

.. _extensionManager-googleMapsLibrary:

googleMapsLibrary
"""""""""""""""""

This is the link to the current Google Maps JavaScript Api. It is configured as **wrap** so that you
can decide where the ApiKey has to be inserted.

.. important::
   This configuration is only for Google Maps which are used in list module of TYPO3 Backend.

.. _extensionManager-useHttps:

useHttps
""""""""

If you activate that checkbox we will replace the scheme (maybe: http) of googleMapsLibrary to https.
If it is deactivated we will convert it back to http.

As you can change that option on your own in googleMapsLibrary it could be, that we will remove that option in future.

.. _extensionManager-googleMapsJavaScriptApiKey:

googleMapsJavaScriptApiKey
""""""""""""""""""""""""""

Since version 2.0.0 this extension needs a Google Maps JavaScript ApiKey which you have to get
over `Google Console<http://console.developers.google.com>`_

.. _extensionManager-googleMapsGeocodeApiKey:

googleMapsGeocodeApiKey
"""""""""""""""""""""""

Since version 2.0.0 this extension needs a Google Maps Geocode ApiKey, if you use the CityMap plugin.
It was needed to get Latitude and Longitude from a given address.

Please visit `Google Console <http://console.developers.google.com>`_ to get one.

.. _extensionManager-defaultLatitude:

defaultLatitude
"""""""""""""""

Default latitude

.. _extensionManager-defaultLongitude:

defaultLongitude
""""""""""""""""

Default longitude

.. _extensionManager-defaultRadius:

defaultRadius
"""""""""""""

Default radius

.. _extensionManager-infoWindowContentTemplatePath::

infoWindowContentTemplatePath
"""""""""""""""""""""""""""""

You can define your own default template for these little info window content when clicking on a marker.
Further you can override this template path again with TypoScript at settings.infoWindowContentTemplatePath = [path]

.. _extensionManager-strokeColor::

strokeColor
"""""""""""

Stroke color

.. _extensionManager-strokeOpacity:

strokeOpacity
"""""""""""""

Stroke opacity

.. _extensionManager-strokeWeight:

strokeWeight
""""""""""""

Stroke weight

.. _extensionManager-fillColor:

fillColor
"""""""""

Fill color

.. _extensionManager-fillOpacity:

fillOpacity
"""""""""""

Fill opacity

