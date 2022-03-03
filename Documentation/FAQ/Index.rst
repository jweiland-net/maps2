.. include:: ../Includes.txt


.. _faq:

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

.. code-block:: html

   <div id="maps2-{data.uid}"
        class="maps2"
        data-name="maps2"
        data-environment="{environment -> maps2:convertToJson()}"
        data-pois="{poiCollections -> maps2:convertToJson()}"></div>


In this example the `data-name` is `maps2`, so we have to create a service with name `maps2`:

.. code-block:: javascript

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

.. code-block:: javascript

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
