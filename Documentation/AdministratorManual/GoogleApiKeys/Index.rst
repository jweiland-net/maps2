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

#. Create a new project at the upper left of the console.

.. figure:: /Images/GoogleApiKeys/google-create-project.jpg
   :width: 500px
   :align: left
   :alt: DashBoard of Google Console

#. Choose a project name

#. Click on ``Create``

.. figure:: /Images/GoogleApiKeys/google-create-project-form.jpg
   :width: 500px
   :align: left
   :alt: Create new project form

#. Wait a second or two until the notification icon at the upper right will stop turning around.

#. If your new project was not opened automatically, please choose your project from the project list at the upper
   left. If it was not listed there, click on ``Show more projects``.

#. Click on ``Activate API``

.. figure:: /Images/GoogleApiKeys/google-activate-api.jpg
   :width: 500px
   :align: left
   :alt: Activate your first API Key

Get Google Maps JavaScript Api Key
""""""""""""""""""""""""""""""""""

#. Choose ``Google Maps JavaScript API``

.. figure:: /Images/GoogleApiKeys/google-link-javascript-api.jpg
   :width: 500px
   :align: left
   :alt: Link to create API for Google Maps JavaScript API

#. ``Activate`` the Google Maps JavaScript API

.. figure:: /Images/GoogleApiKeys/google-activate-javascript-api.jpg
   :width: 500px
   :align: left
   :alt: Activate Google Maps JavaScript API

#. Add authentication to Google Maps JavaScript API

.. figure:: /Images/GoogleApiKeys/google-add-authentication-javascript.jpg
   :width: 500px
   :align: left
   :alt: Add authentication for Google Maps JavaScript API

#. Get API Key and assign reduced access rights

.. figure:: /Images/GoogleApiKeys/google-get-key-reduce-rights.jpg
   :width: 500px
   :align: left
   :alt: Get API Key and reduce access rights

#. Give it a name like ``JavaScript``, select ``HTTP-Verweis (Website)`` and set a domain name like ``www.example.com``.
   Of cause you can also use something like ``*.example.com``. Else everybody can use your API Key.

.. figure:: /Images/GoogleApiKeys/google-add-access-rights-javascript.jpg
   :width: 500px
   :align: left
   :alt: Add access rights to your Google Maps JavaScript API Key

#. Click on ``save`` to finish the wizard

Get Google Maps Geocoding Api Key
"""""""""""""""""""""""""""""""""

Nearly the same as above. Start again from Dashboard.

#. Click on ``Activate API``

.. figure:: /Images/GoogleApiKeys/google-activate-api.jpg
   :width: 500px
   :align: left
   :alt: Activate your first API Key

#. Choose ``Google Maps Geocoding API``

.. figure:: /Images/GoogleApiKeys/google-link-geocoding-api.jpg
   :width: 500px
   :align: left
   :alt: Link to create API for Google Maps Geocoding API

#. ``Activate`` the Google Maps Geocoding API

#. Click on ``Authentication`` in the left menu and then on ``Create new login data``.

.. figure:: /Images/GoogleApiKeys/google-add-new-authentication-geocode.jpg
   :width: 500px
   :align: left
   :alt: Add new authentication for Geocode API Key

#. Choose authentication wizard

.. figure:: /Images/GoogleApiKeys/google-choose-authentication-wizard.jpg
   :width: 500px
   :align: left
   :alt: Choose Authentication wizard

#. Add Authentication for Geocoding API

.. figure:: /Images/GoogleApiKeys/google-add-authentication-geocode.jpg
   :width: 500px
   :align: left
   :alt: Choose Authentication wizard

#. Get API Key and reduce access rights

.. figure:: /Images/GoogleApiKeys/google-get-key-reduce-rights-geocode.jpg
   :width: 500px
   :align: left
   :alt: Get API Key and create access rights for Geocoding API Key

#. Give it a name like ``Geocode``, select ``IP-Address (Webserver)`` and set the IP-Address of your server.
   Else everybody can use your API Key.

.. figure:: /Images/GoogleApiKeys/google-add-access-rights-geocode.jpg
   :width: 500px
   :align: left
   :alt: Reduce access rights to your Geocode API Key

#. Click ``Save`` to finish the wizard.
