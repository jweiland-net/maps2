.. include:: /Includes.rst.txt


..  _howToStart:

============
How to start
============

This walkthrough will help you to implement the extension maps2 at your
TYPO3 site. The installation is covered :ref:`here <installation>`.

..  only:: html

..  contents::
    :local:
    :depth: 1

..  _howToStartCreateRecords:

Create the records
==================

Before any maps2 record can be shown in the frontend those need to be
created.

#.  Create a new sysfolder.
    (Of course you can also use an existing sysfolder).

#.  Switch to **List module**

#.  Use the icon in the topbar "Create new record" and search for "Maps2" and its
    record "Marking".

#.  Click on "Marking" to create a new maps2 record.

#.  Give it a title (required)

#.  Choose a type of record you want to create. This will reload the form to show further fields especially for this
    record type. Alternative you can click on one of these type images to switch the type.

#.  After reload you should see some more tabs now. Click on tab "Map"

#.  Field "Address" is required, but you can not fill it. Please use the search field at top of map to search
    for an address and press <Return>. If an address was found, we will automatically fill the required address field
    in a formatted representation of the searched address.

..  _howToStartAddPlugin:

Add a plugin to a page
======================

A plugin is used to render a defined selection of records in the frontend.
Follow this steps to add a plugin to a page:

Maps2: Show map - Plugin
------------------------

#.  Create a new page with a title like "Location" which will be used to show
    a Marking record.

#.  Add a new content element, switch to Tab "Plugins" and select the entry "Maps2: Show map"

#.  Change the 1 :sup:`st` field to one of your created Marking records.

#.  Save the plugin.

Here are some more possible configuration of the plugin:

If you leave marking and category field empty we will show all POIs of configured StorageFolder

If you leave marking field empty, but select some categories we will show all POIs of selected category. As long
as they are available in configured StorageFolder.

If marking and category field is set, marking field has precedence.

If there is a PoiCollection UID in URI this POI has precedence over marking and category settings.

Info: If there will be shown more than one POI on the map it is not possible to set zoom anymore. We are
working with the BoundedBox feature of Google Maps and OpenStreetMap to zoom out until all POIs are visible.

Maps2: Search Radius - Plugin
-----------------------------

#.  Create a new page with a title like "Radius Search" which will be used to show
    a form, where a website visitor can enter an address and a range to search for Markings.

#.  Add a new content element of type "General Plugin"

#.  Switch over to tab "Plugin" and select "Maps2: Search Radius" from selectbox "Selected Plugin"

#.  After reload go to Tab "Plugin" again and change the 1 :sup:`st` field to a location where you want to search.
    Something like "germany" or a city name.

#.  Save the plugin.

Maps2: City Map - Plugin
------------------------

#.  Create a new page with a title like "City Map" which will be used to show
    a form, where a website visitor can enter a street name.

#.  Add a new content element of type "General Plugin"

#.  Switch over to tab "Plugin" and select "Maps2: City Map" from selectbox "Selected Plugin"

#.  After reload go to Tab "Plugin" again and Change the 1 :sup:`st` field to location where you want to search. Type
    in a city name and the results will be reduced to that city.

#.  Save the plugin.
