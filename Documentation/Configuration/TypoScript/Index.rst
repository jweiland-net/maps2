..  include:: /Includes.rst.txt


..  _typoScript:

==========
TypoScript
==========

All following TypoScript configuration consists in `plugin.tx_maps2`

..  hint::

    We prefer using the Site Settings to configure maps2 since TYPO3 13.


view
====

templateRootPaths
-----------------

Default: `EXT:maps2/Resources/Private/Templates/`

Example: `plugin.tx_maps2.view.templateRootPaths.40 = EXT:site_package/Resources/Private/Templates/`

You can override our Templates with your own SitePackage extension.

partialRootPaths
----------------

Default: `EXT:maps2/Resources/Private/Partials/`

Example: `plugin.tx_maps2.view.partialRootPaths.40 = EXT:site_package/Resources/Private/Partials/`

You can override our Partials with your own SitePackage extension.

layoutsRootPaths
----------------

Default: `EXT:maps2/Resources/Private/Layouts/`

Example: `plugin.tx_maps2.view.layoutsRootPaths.40 = EXT:site_package/Resources/Private/Layouts/`

You can override our Layouts with your own SitePackage extension. We prefer to
change this value in TS Constants.


persistence
===========

storagePid
----------

Default: empty

Example: `plugin.tx_maps2.persistence.storagePid = 12,32,48`

Set this value to a Storage Folder where you have stored the event records.

..  important::

    If you have stored Organizers and Locations in another Storage Folder, you
    have to add theses PIDs here, too.

..  tip::

    If you use creation of events over frontend plugin, new records will be
    stored in first PID found in storagePid. To store record in other storage
    PIDs you need following configuration

    ..  code-block:: typoscript

        plugin.tx_maps2.persistence.classes.JWeiland\maps2\Domain\Model\Event.newRecordStoragePid = 34
        plugin.tx_maps2.persistence.classes.JWeiland\maps2\Domain\Model\Location.newRecordStoragePid = 543

settings
========

overlay.link.addSection
-----------------------

Default: 1

Example: `plugin.tx_maps2.settings.overlay.link.addSection = 0`

Append URI section to link of button in consent template. Useful to jump
directly to the content element record with maps2 plugin.

With option set to 1: [currentURI]/mapProviderRequestsAllowedForMaps2=1#c123

With option set to 0: [currentURI]/mapProviderRequestsAllowedForMaps2=1

infoWindowContentTemplatePath
-----------------------------

Example: `plugin.tx_maps2.settings.infoWindowContentTemplatePath = EXT:your_sitepackage/Resources/Templates/InfoWindowContent.html`

Here you can define your own Fluid-Template for these little PopUps of Markers.

Since maps2 9.2.0 you have access to all related foreign records of your
PoiCollection in Template.
Use: `<f:for each="{poiCollection.foreignRecords}" as="foreignRecord">...</f:for>`

As such a PoiCollection can be assigned to multiple different tables like
tt_address, news, what ever, you can differ between the foreign records
with f.e.:

..  code-block:: html

    <f:groupedFor each="{poiCollection.foreignRecords}" as="groupedForeignRecords" groupBy="jwMaps2TableName" groupKey="tableName">
      <div>Table: {tableName}</div>
      <ul>
        <f:for each="{groupedForeignRecords}" as="foreignRecord">
          <li>PoiCollection URL: {foreignRecord.url}</li>
        </f:for>
      </ul>
    </f:groupedFor>

`jwMaps2TableName` and `jwMaps2ColumnName` are two special keys we have added to each foreign record.

infoWindow.image.width
----------------------

Default: 150c

Example: `plugin.tx_maps2.settings.infoWindow.image.width = 300`

Set the maximum width of images within the InfoWindow PopUp

infoWindow.image.height
-----------------------

Default: 150c

Example: `plugin.tx_maps2.settings.infoWindow.image.height = 180c`

Set the maximum height of images within the InfoWindow PopUp

markerClusterer.enable
----------------------

Only available for Google Maps

Default: 0

Example: `plugin.tx_maps2.settings.markerClusterer.enable = 1`

This value is configurable through TypoScript Constants Editor

If you work with a lot of poi collection records you can activate the marker
clusterer. The marker clusterer will merge multiple poi collections to 1 icon
with the contains amount of records.

markerClusterer.imagePath
-------------------------

Only available for Google Maps

Default: `EXT:maps2/Resources/Public/Icons/MarkerClusterer/m`

Example: `plugin.tx_maps2.settings.markerClusterer.imagePath = EXT:my_sitepackage/Resources/Public/Icons/MarkerClusterer/m`

If you don't like the icons of Marker Clusterer you can choose a different path for your own
images.


_LOCAL_LANG
===========

As an integrator you can override each language key with TypoScript. For
frontend maps2 uses this file:

`EXT:maps2/Resources/Private/Language/locallang.xlf`

Example: `plugin.tx_maps2._LOCAL_LANG.de.listMyEvents = Zeige meine Veranstaltungen`
