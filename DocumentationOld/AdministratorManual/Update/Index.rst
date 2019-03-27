.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.rst.txt

Updating
--------
If you update EXT:maps2 to a newer version, please read this section carefully!

Update to Version 4.0.0
^^^^^^^^^^^^^^^^^^^^^^^

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
^^^^^^^^^^^^^^^^^^^^^^^

We have removed TYPO3 6.2 compatibility completely.

In f.e. germany it is not allowed to send the users ip address without
his confirmation. That's why we have added a new extension management configuration
which can output a little form, where the user can accept sending his information
to third party servers like Google to display the maps. This new feature
touches nearly all methods, so, if you have extended maps2, please pre-check the new
widget templates and actions. Maybe it's good to have a look into the new GoogleMapsService class.

Update to Version 2.5.0
^^^^^^^^^^^^^^^^^^^^^^^

With version 2.5.0 we have solved a camelcase problem of the cache table.
It was renamed from cf_maps2_cachedHtml to cf_maps2_cachedhtml. Please delete
the old tables cf_maps2_cachedHtml and cf_maps2_cachedHtml_tags, deactivate maps2
in extension manager and activate it again.

.. important::

   It does not help to rename these tables only.

Update to Version 2.0.0
^^^^^^^^^^^^^^^^^^^^^^^

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

