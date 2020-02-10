.. include:: ../../Includes.txt

Updating
========

If you update EXT:maps2 to a newer version, please read this section carefully!

Update to Version 8.0.0
-----------------------

As a normal user you can update to this version without any problems.

We have changed the SignalSlot preIsRecordAllowedToCreatePoiCollection. It does not allow returning $isValid as 4th
parameter anymore. As $isValid is a reference now, please change it directly and prevent your SignalSlot to return
anything.

There is no Debug Output of Map Provider response in Backend anymore, if request fails. We have added more detailed
error messages instead. As a Dev, you can access all Messages of Client and GeoCodeService directly.

Update to Version 7.0.0
-----------------------

As a normal user you can update to this version without any problems.

As an extension developer who has modified maps2 you should read following lines:

We have removed ModifyMarker class.
--> Please update lat, lng and radius fields in BE form with JS directly.

We have removed modifyMarkerInDb function in our Map Provider JS files for BE modules.
--> Please update lat, lng and radius fields in BE form with JS directly.

We have removed AjaxController.
--> As it was not used since months, it was not used anymore.

We have removed all extbase usage from all Ajax classes and have rewritten them completely with Doctrine.
--> Please check your extension and check if an update is needed.

Update to Version 6.1.0
-----------------------

As mouseScrollWheelZoom is not available for all map providers you have to execute the Update Wizard
to move this Option in FlexForm from Google Maps sheet to MapOptions sheet.

Update to Version 6.0.0
-----------------------

The current CacheIdentifier for InfoWindowContent is not save for multilingual environments.
That way we have removed cacheIdentifier property from all Cache ViewHelpers and added the new property poiCollection.
It helps us to build a more unique CacheIdentifier with GeneralUtility::stdAuthCode()

You have to update all of your templates where our Cache ViewHelpers are used. In most cases only
InfoWindowContent.html has to be modified. Please remove cacheIdentifier from all Cache ViewHelpers and add
poiCollection instead:

Before: ``<m:cache.setCache cacheIdentifier="htmlCode{poiCollection.uid}" data="{content -> f:format.raw()}" />``
After: ``<m:cache.setCache data="{content -> f:format.raw()}" poiCollection="{poiCollection}" />``

Please you are interested into Cache ViewHelper properties, please have a look into our updated Documentation.

Update to Version 5.1.0
-----------------------

We have removed the hard-coded map provider settings from VH Widgets and added these to TS-Template. So please
check your maps2 output and/or individual JS, if our Widget VHs are still working for you.

If you don't make use of our Widget ViewHelpers there should be no problem with this update.

Update to Version 5.0.0
-----------------------

We have added an Open Street Map Implementation.
To differ between them we have added two new static templates. One for Google Maps and one for Open Street Map. You
have to keep the Default static template, but you have to add one of the other static templates.

There is a new Option called `mapProvider` in ExtensionManager. Please set the `mapProvider` and `default mapProvider`
to your needs.

We have moved some Google Maps fields in FlexForm to another sheet. To prevent duplicates in DB please execute
Update Wizard in Installtool.

We have removed automatic registering tx_maps2_uid column for tt_address. Please take a look into the example of
Maps2 Registry to see how it works.

As we have removed our API class GoogleMapsService completely you now have to use the API methods
in MapService and GeoCodeService instead.

`getPositionsByAddress` returns an ObjectStorage containing Position objects instead of RadiusResult objects now.
`getFirstFoundPositionByAddress` return an object of type Position now.

Update to Version 4.0.0
-----------------------

We have added some new fields to maps2. So please go into Extensionmanager
and open the configuration. Please check, if everything matches your needs and safe
the configuration.

You have to clear the system cache, because of new fields in TCA.

We have renamed the field ``marker_icon`` from table ``sys_category`` into ``maps2_marker_icons``
and switched to FAL related images. Please execute Update script in Extensionmanager for ``maps2``
to migrate your old images.

We have moved all JavaScript Code from ``page.includeJSFooter`` to ``page.includeJSFooterlibs``, so
now you have better options to override or append our/your custom JavaScript in TypoScript.

All methods of MapService have been migrated into GoogleMapsService.
GeocodeUtility have been deleted. Please use getPositionsByAddress or getFirstFoundPositionByAddress
of GoogleMapsService.

Update to Version 3.0.0
-----------------------

We have removed TYPO3 6.2 compatibility completely.

In f.e. germany it is not allowed to send the users ip address without
his confirmation. That's why we have added a new extension management configuration
which can output a little form, where the user can accept sending his information
to third party servers like Google to display the maps. This new feature
touches nearly all methods, so, if you have extended maps2, please pre-check the new
widget templates and actions. Maybe it's good to have a look into the new GoogleMapsService class.

Update to Version 2.5.0
-----------------------

With version 2.5.0 we have solved a camelcase problem of the cache table.
It was renamed from cf_maps2_cachedHtml to cf_maps2_cachedhtml. Please delete
the old tables cf_maps2_cachedHtml and cf_maps2_cachedHtml_tags, deactivate maps2
in extension manager and activate it again.

.. important::

   It does not help to rename these tables only.

Update to Version 2.0.0
-----------------------

Version 2.0.0 needs a Google Maps JavaScript ApiKey which has to be inserted in
maps2 configuration of Extensionmanager (for BE usage) and in constants section
of your TypoScript-Template (for FE usage). That's why you have to insert the
static template **Maps2 (maps2)** in your TypoScript Template now.

Furthermore we have updated the FlexForm of maps2 and removed the option for
SwitchableControllerActions. With version 2.1.2 we have added an Update-Wizard
in Extensionmanager which can do that job for you. In prior versions you have to
remove that setting of each plugin in tt_content record field pi_flexform on your own.

.. important::

   It does not help to open and save the record in backend!

