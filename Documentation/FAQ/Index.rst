..  include:: /Includes.rst.txt


..  _faq:

===
FAQ
===

DB compatibility
================

In most cases EXT:maps2 uses the QueryBuilder to query data, but in case of Plugin `maps2_searchwithinradius`
we need to execute a native MySQL query without QueryBuilder to find the related POIs. In that special case
MySQL/MariaDB is mandatory.

Consent Tools
=============

If you want to use external consent tools you should deactivate both options
`explicitAllowMapProviderRequests` and `explicitAllowMapProviderRequestsBySessionOnly` in
extension manager.

Klaro
-----

Overwrite Templates path with help of TypoScript and copy `Templates/PoiCollection/Show.html` into your
SitePackage extension. Add `data-name` attribute to existing div-tag.

..  code-block:: html

    <div id="maps2-{data.uid}"
         class="maps2"
         data-name="maps2"
         data-environment="{environment -> maps2:convertToJson()}"
         data-pois="{poiCollections -> maps2:convertToJson()}"></div>


In this example the `data-name` is `maps2`, so we have to create a service with name `maps2`:

..  code-block:: javascript

    {
        name: 'maps2',
        default: false,
        title: 'Google Maps/Open Street Map',
        // contextualConsentOnly: true,
        optOut: false,
        required: false,
        purposes: ['analytics'],
    },


In some cases it may happen, that the map will be displayed partly. To prevent that problem a reload after accepting
the consent may help. Create a new js file which will be loaded AFTER `klaro.js`. Keep the name of
`data-name`, in this case `maps2` here, too:

..  code-block:: javascript

    let manager = klaro.getManager();
    manager.watch({
        update: function(manager, eventType, data) {
            if (
                eventType === 'saveConsents'
                && data.consents.maps2 === true
            ) {
                window.location.reload();
            }
        }
    });

mindshape_cookie_consent
------------------------

..  rst-class:: bignums

    1.  Add cookie record

        Edit cookie consent record on your root page, add/edit cookie-category and add new Cookie record.

        *   Set `identifier` to `google_maps`
        *   Set `Cookie name` to `mapProviderRequestsAllowedForMaps2`
        *   Set `Cookie lifetime` to `86400`
        *   Fill the other values to your needs.

    2.  Copy Show.html to SitePackage

        Copy file `EXT:maps2/Resources/Private/Extensions/maps2/Templates/PoiCollection/Show.html` to a
        directory in your SitePackage extension. For example:

        `typo3conf/ext/site_package/Resources/Private/Extensions/maps2/Templates/PoiCollection/Show.html`

    3.  Update Show.html

        Add ViewHelpers of EXT:mindshape_cookie_consent in Show.html

        ..  code-block:: html

            <html lang="en"
                  xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
                  xmlns:maps2="http://typo3.org/ns/JWeiland/Maps2/ViewHelpers"
                  xmlns:ms="http://typo3.org/ns/Mindshape/MindshapeCookieConsent/ViewHelpers"
                  data-namespace-typo3-fluid="true">

        Add JavaScript files with help of mindshape ViewHelper below the <div> tag in Show.html:

        ..  code-block:: html

            <ms:consent identifier="google_maps"
                        scripts="{
                            0: '//maps.googleapis.com/maps/api/js?key={settings.googleMapsJavaScriptApiKey}&libraries=places',
                            1: '{f:uri.resource(path:\'EXT:/maps2/Resources/Public/JavaScript/GoogleMaps2.js\')',
                            2: '{f:uri.resource(path:\'EXT:site_package/Resources/Public/JavaScript/GoogleMaps.js\')}'
                        }">
            </ms:consent>

    4.  Initialize Google Maps

        Create a new file in your site_package:

        `/typo3conf/ext/site_package/Resources/Public/JavaScript/GoogleMaps.js`

        add just one line to that file:

        `initMap();`

        If you choose another file path, please update it in Show.html at position 2 of the ViewHelper of step 3.

    5.  Set template path to SitePackage

        Update TypoScript constant of maps2 `templateRootPath` as following:

        `plugin.tx_maps2.view.templateRootPath = EXT:site_package/Resources/Private/Extensions/maps2/Templates/`

    6.  Make API key available for Fluid

        Add following to TypoScript setup

        ..  code-block:: typoscript

            plugin.tx_maps2 {
              settings {
                googleMapsJavaScriptApiKey = {$plugin.tx_maps2.view.googleMapsJavaScriptApiKey}
              }
            }

    7.  Deactivate existing JavaScript

        ..  code-block:: typoscript

           page.includeJSFooterlibs.maps2 >
           page.includeJSFooterlibs.googleMapsForMaps2 >

    8.  You're done
