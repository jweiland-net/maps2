.. include:: ../Includes.txt


.. _changelog:

ChangeLog
=========

**Version 9.3.6**

- Update TS condition for requireCHashArgumentForActionArguments

**Version 9.3.5**

- Update old TS condition for requireCHashArgumentForActionArguments

**Version 9.3.4**

- Repair AJAX calls on POI click

**Version 9.3.3**

- Use RequestBuilder instead of ConfigurationManager to retrieve extKey

**Version 9.3.2**

- Add title parameter to EditPoi VH

**Version 9.3.1**

- Bugfix: Remove strict types from ReadOnly Input text element to be in sync with class from core

**Version 9.2.1**

- Bugfix: Fallback to "footway", if "road" was not found in OSM response

**Version 9.2.0**

- Feature: Add all related foreign records to PoiCollection
- Feature: New SignalSlot to modify related foreign records before adding them to PoiCollection

**Version 9.1.0**

- Feature: Allow Info Window for all Map Types

**Version 9.0.1**

- Replace "var" with "let" in OpenStreetMap Module JS
- Use same scheme from current request for MapTiles in OpenStreetMap Module JS

**Version 9.0.0**

- First version which is compatible with TYPO3 10.
- Removed TYPO3 8.7 compatibility
- Replace Extbase @validate annotations
- Replace Extbase @lazy and @cascade Annotations
- Remove old Form Elements for TYPO3 8.7
- Make use of new ExtensionConfiguration class
- Reduce ext_tables.sql to business columns only
- Use Symfony expressions in TypoScript
- Add strict types where possible
- Adopt UnitTests

**Version 8.0.1**

- Restructured GoogleMapsModule JS file to be more compatible with requireJS

**Version 8.0.0**

- SignalSlot preIsRecordAllowedToCreatePoiCollectionCatch does not allow returning $isValid as 4th parameter anymore
- Add Message Handling to Map Provider Clients.

**Version 7.1.5**

- Catch Exception for TYPO3 8 in ArrayUtility of sysext core.

**Version 7.1.4**

- Check FlexForm for each entry before processing them in UpgradeWizard

**Version 7.1.3**

- Set previous RenderingContext in edit POI Widget to have access to ViewHelperVariableContainer

**Version 7.1.2**

- Do not build Request of foreign extensions, to check, if map should be shown or not.

**Version 7.1.1**

- As references can not be passed to call_user_func in SignalSlot Dispatcher, we have to use
  the SlotReturn value to get new value of isValid.

**Version 7.1.0**

- Add possibility to pre-filter records with help of Maps2Registry API before attaching a PoiCollection record
- Add possibility to pre-filter records with help of a SignalSlot before attaching a PoiCollection record
- Update Documentation

**Version 7.0.0**

- Remove AjaxController. There is no need anymore after switch to AjaxDispatcher.
- Remove ModifyMarker Ajax Request, as we update position fields in BE form directly.
- Remove all extbase classes from Ajax Calls and switch over to Doctrine
- Update Documentation

**Version 6.1.0**

- Option mouseScrollWheelZoom is now available for Google Maps and OpenStreetMap
- All FlashMessages are now created by MessageHelper
- All FlashMessages are now stored in Session

**Version 6.0.0**

- Breaking: Removed cacheIdentifier property from all Cache ViewHelpers
- Bugfix: Create better multilingual CacheIdentifier for InfoWindow content.
- Feature: New CacheService to manage CacheIdentifiers and CacheTags
- Task: Remove CacheEntry after storing of PoiCollection in Backend with help of "flushByTag" instead of "remove"
- Update Documentation of Cache ViewHelpers
- Bugfix: Clear InfoWindowContent Cache for our own records, too.

**Version 5.3.1**

- Bugfix: Wrong HTML id in AllowMapForm as {data} was not assigned in MapService
- Task: Remove mapHeight und mapWidth from AllowMapForm template

**Version 5.3.0**

- Task: Update address in PoiCollection if necessary
- Feature: In AddressHelper you can now check a formatted address against foreignLocationRecord
- Bugfix: Clear Maps2 HTML Cache after a PoiCollection was saved.

**Version 5.2.10**

- Bugfix: Repair checkForUpdate in Flexform migration class
- Bugfix: Store FlashMessages in Session for BE context

**Version 5.2.9**

- Bugfix again: Do not try to update empty pi_flexform columns in Wizard

**Version 5.2.8**

- Bugfix: Do not try to update empty pi_flexform columns in Wizard

**Version 5.2.7**

- Bugfix: Add extKey to getConfiguration() settings of maps2 only

**Version 5.2.6**

- Documentation: Repair links to Cache VHs and Widget VHs in Documentation

**Version 5.2.5**

- Documentation: Better explaination of using Maps2Registry
- Bugfix: Better check to prevent loading session again

**Version 5.2.4**

- UniTest: Update secure of slack notification in Travis
- Bugfix: Fill cruser_id with be_user ID if set, else with 0

**Version 5.2.3**

- Bugfix: Add compatibility for UpdateWizard in TYPO3 8 and 9

**Version 5.2.2**

- Bugfix: Remove quotes from geocode uri
- Bugfix: Disable compression for js files with get parameters
- Bugfix: Re-check, if maps2 cache is initializable in Maps2Registry

**Version 5.2.1**

- Bugfix: Correct wrong geocode URIs in ext_conf_template

**Version 5.2.0**

- Feature: Show all PoiCollections of a StorageFolder
- Feature: Force set zoom level
- Feature: Add possibility to change Geocode API URI of map providers

**Version 5.1.0**

- Make use of initializeArguments in VH Widgets
- Add plugin defaults to TS-Templates
- Correct merging of settings in VH Widgets

**Version 5.0.3**

- Repair zoom level settings for OSM

**Version 5.0.0**

- Rewritten documentation
- Add new ext_icon as SVG
- Add OpenStreetMap Implementation
- Update various translations
- Add placeholders to style settings in TCA
- Add mapProvider to switch between GM and OSM in Backend
- Add static templates for GM and OSM
- Remove automatic maps2 registry for tt_address

**Version 4.3.5**

- Set default of relation columns to 0. strict_type.

**Version 4.3.4**

- Check against NULL before unserialize ExtConf

**Version 4.3.3**

- Replace deprecated placeIdOnly with setFields in Google Maps JS

**Version 4.3.2**

- Implement better record icons

