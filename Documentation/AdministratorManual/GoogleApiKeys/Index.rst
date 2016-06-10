.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../_IncludedDirectives.rst

Google Api Keys
---------------

.. only:: html

	Since version 2.0.0 this extension will only work with Google Api Keys.
	This documentation will show you step by step how to get the **Google Maps JavaScript API Key**
	and the **Google Maps GeoCoding API Key** from Google Console.

Google Console
^^^^^^^^^^^^^^
#. If you don't have a Google Account you have to register as new User.

#. Open `Google Console <https://console.developers.google.com/>`_.

#. Create a new project at the upper right of the console.

.. figure:: /Images/GoogleApiKeys/google-create-project.png
   :width: 500px
   :align: left
   :alt: DashBoard of Google Console

#. Choose a project name

#. Click on extended options, if you want to change the location of the App Engine.
   As a european I prefer to set this to ``europe-west``

#. Click on ``Create``

.. figure:: /Images/GoogleApiKeys/google-create-project-form.png
   :width: 500px
   :align: left
   :alt: Create new project form

#. Wait a second or two until you see the Console again

Get Google Maps JavaScript Api Key
""""""""""""""""""""""""""""""""""

#. Choose ``Google Maps JavaScript API``

.. figure:: /Images/GoogleApiKeys/google-link-javascript-api.png
   :width: 500px
   :align: left
   :alt: Link to create API for Google Maps JavaScript API

#. ``Activate`` the Google Maps JavaScript API

.. figure:: /Images/GoogleApiKeys/google-activate-javascript-api.png
   :width: 500px
   :align: left
   :alt: Activate Google Maps JavaScript API

#. Assign an authentication to your just created API

.. figure:: /Images/GoogleApiKeys/google-login-javascript-api.png
   :width: 500px
   :align: left
   :alt: Add Authentication

#. Start the wizard to add an authentication to your API. Give it a name like ``JavaScript``
   and set a domain name like ``*.your-domain.com/*``. Else everybody can use your Api Key.

.. figure:: /Images/GoogleApiKeys/google-create-javascript-api.png
   :width: 500px
   :align: left
   :alt: Start authentication wizard

#. Finish the wizard

.. figure:: /Images/GoogleApiKeys/google-javascript-api-finished.png
   :width: 500px
   :align: left
   :alt: Finish the authentication wizard

Get Google Maps Geocoding Api Key
"""""""""""""""""""""""""""""""""

Nearly the same as above.

#. Choose ``Google Maps Geocoding API``

.. figure:: /Images/GoogleApiKeys/google-link-geocoding-api.png
   :width: 500px
   :align: left
   :alt: Link to create API for Google Maps Geocoding API

#. ``Activate`` the Google Maps Geocoding API

#. Assign an authentication to your just created API

#. Choose authentication wizard

.. figure:: /Images/GoogleApiKeys/google-choose-login.png
   :width: 500px
   :align: left
   :alt: Choose authentication wizard

#. Start the wizard to add an authentication to your API. Give it a name like ``GeoCoding``,
   choose ``webserver`` from selectbox and set an ip address. Else everybody can use your Api Key.

.. figure:: /Images/GoogleApiKeys/google-login-geocoding-api.png
   :width: 500px
   :align: left
   :alt: Start authentication wizard

#. Attach an ip address to your authentication. Else everybody can use your Api Key.

.. figure:: /Images/GoogleApiKeys/google-login-ip-geocoding-api.png
   :width: 500px
   :align: left
   :alt: Attach ip address

#. Finish the wizard

#. Back in the overview you can see the registered Api Keys

.. figure:: /Images/GoogleApiKeys/google-api-keys.png
   :width: 500px
   :align: left
   :alt: Show Google Api Keys
