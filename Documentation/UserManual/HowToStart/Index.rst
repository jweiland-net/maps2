.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../_IncludedDirectives.rst

.. _howToStart:

How to start
============
This walkthrough will help you to implement the extension maps2 at your
TYPO3 site. The installation is covered :ref:`here <installation>`.

.. only:: html

.. contents::
        :local:
        :depth: 1

.. _howToStartCreateRecords:

Create the records
------------------
Before any maps2 record can be shown in the frontend those need to be
created.

#. Create a new sysfolder and switch to the list module. (Of
   course you can also use an existing sysfolder).

#. Switch to **List module**

#. Use the icon in the topbar "Create new record" and search for "Maps2" and its
   record "Marking".

#. Click on "Marking" to create a new maps2 record. Fill as many fields you
   want to field, a required one is only the title.

.. _howToStartAddPlugin:

Add a plugin to a page
----------------------
A plugin is used to render a defined selection of records in the frontend.
Follow this steps to add a plugin to a page:

Google Maps Plugin
^^^^^^^^^^^^^^^^^^

#. Create a new page with a title like "Location" which will be used to show
   the Marking record.

#. Add a new content element and select the entry "Google Maps"

#. Switch to the tab "Plugin" where you can define the plugins settings.

   #. Change the 1 :sup:`st` field to one of your created Marking records.

   #. Save the plugin.

Maps2 Maps: Search Radius Plugin
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

#. Create a new page with a title like "Radius Search" which will be used to show
   a form, where a website visitor can enter an address and a range to search for Markings.

#. Add a new content element and select the entry "Google Maps: Search Radius"

#. Switch to the tab "Plugin" where you can define the plugins settings.

   #. Change the 1 :sup:`st` field to location where you want to search. Something like "germany" or a city name.

   #. Save the plugin.

Maps2 Maps: City Map Plugin
^^^^^^^^^^^^^^^^^^^^^^^^^^^

#. Create a new page with a title like "City Map" which will be used to show
   a form, where a website visitor can enter a street name.

#. Add a new content element and select the entry "Google Maps: City Map"

#. Switch to the tab "Plugin" where you can define the plugins settings.

   #. Change the 1 :sup:`st` field to location where you want to search. Type in a city name and the results will be reduced to that city.

   #. Save the plugin.
