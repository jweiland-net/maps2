:navigation-title: Form element

..  include:: /Includes.rst.txt
..  _configuration-form-element:

======================================================
Form element "Maps2PoiPosition" for the form framework
======================================================

..  versionadded:: 12.3.0

    maps2 provides the form element `Maps2PoiPosition` for forms based on
    the TYPO3 form framework (EXT:form).

The form element `Maps2PoiPosition` shows a map with a draggable marker in
a frontend form. Frontend users can correct a position, for example the
position, which was found by geocoding the address they have entered on a
previous page of the form. Dragging the marker or clicking into the map
moves the marker and updates the position.

This chapter describes, how integrators add the element to a form
definition and configure it. The chapter
:ref:`developer-form-element` describes the value of the element and how
an extension geocodes an address and stores the position.

..  contents::
    :local:


..  _configuration-form-element-use-cases:

When to use the form element "Maps2PoiPosition"
===============================================

Use the element, if frontend users create or edit records with an address
and the position on the map matters, for example companies, locations or
events. A geocoded position is not always exact: new buildings, large
premises or ambiguous addresses result in a marker at the wrong place. With
the element the user moves the marker to the correct place before the form
is submitted.

The element does not geocode an address and does not store anything. Your
extension sets the initial position and stores the submitted position. See
:ref:`developer-form-element-integration`.


..  _configuration-form-element-requirements:

Requirements for the form element "Maps2PoiPosition"
====================================================

*   The system extension EXT:form (`typo3/cms-form`) is installed.
*   The site set :guilabel:`Maps2 - Default Set` (`jweiland/maps2-default`)
    is included in the site. It is also included as dependency of the site
    sets :guilabel:`Maps2 - Google Maps` (`jweiland/maps2-googlemaps`) and
    :guilabel:`Maps2 - OpenStreetMap` (`jweiland/maps2-openstreetmap`).

The site set registers the form setup of maps2:

..  code-block:: typoscript
    :caption: EXT:maps2/Configuration/Sets/Maps2/setup.typoscript

    plugin.tx_form.settings.yamlConfigurations {
        1763380200 = EXT:maps2/Configuration/Form/FormSetup.yaml
    }

There is no static TypoScript template, which registers the form setup.
Use the site sets.

If your extension ships a site set with a form, which uses the element, add
`jweiland/maps2-default` to the dependencies of your site set:

..  code-block:: yaml
    :caption: EXT:my_extension/Configuration/Sets/MyExtension/config.yaml

    name: my-vendor/my-extension
    label: 'My extension'
    dependencies:
      - typo3/form
      - jweiland/maps2-default


..  _configuration-form-element-usage:

Add the form element "Maps2PoiPosition" to a form definition
============================================================

Add an element of type `Maps2PoiPosition` to the form definition (YAML
file). Add the validator `NotEmpty`, if the user has to place the marker:

..  code-block:: yaml
    :caption: EXT:my_extension/Resources/Private/Forms/Company.form.yaml

    renderables:
      - type: Page
        identifier: page-map
        label: 'Position on the map'
        renderables:
          - type: Maps2PoiPosition
            identifier: position
            label: 'Position on the map'
            properties:
              markerTitle: 'Your company'
              zoom: 15
              height: 400
              elementDescription: 'Move the marker, if the position is not correct.'
            validators:
              - identifier: NotEmpty

The element works without an initial position, too. Then the marker is
shown at the default position of the
:ref:`extension settings <extensionSettings>` (`defaultLatitude` and
`defaultLongitude`) and the user has to place it. As long as the marker was
not placed, the element has no value and `NotEmpty` fails.

maps2 adds its own validator `Maps2PoiPosition` to each element
automatically. See :ref:`developer-form-element-validation`.


..  _configuration-form-element-properties:

Properties of the form element "Maps2PoiPosition"
=================================================

Set these properties below `properties` of the element. Common properties
of form elements like `elementDescription` work as usual. maps2 ignores
properties it does not know, so your extension can add properties of its
own.

..  confval-menu::
    :name: maps2-poi-position-properties
    :display: table
    :type:
    :default:

    ..  confval:: mapProvider
        :name: maps2-poi-position-map-provider
        :type: string
        :default: (empty)

        The map provider of the map: `gm` (Google Maps) or `osm`
        (OpenStreetMap).

        The property is respected only, if the extension setting
        `mapProvider` is `both`. Else the map provider of the extension
        settings is used. An empty or invalid value uses the extension
        setting `defaultMapProvider`. See :ref:`extensionSettings`.

    ..  confval:: zoom
        :name: maps2-poi-position-zoom
        :type: int
        :default: 15

        The zoom level of the map, from `0` to `20`. Other values use the
        default `15`. The TypoScript setting
        `plugin.tx_maps2.settings.zoom` is not used for this element.

    ..  confval:: height
        :name: maps2-poi-position-height
        :type: int|string
        :default: 400

        The height of the map. Use a number (pixels) or a number with one of
        the units `px`, `em`, `rem`, `vh` or `%`, for example `300`,
        `25rem` or `50vh`. Invalid values use the default `400`.

        The width of the map is always `100%` of the surrounding element.

    ..  confval:: markerTitle
        :name: maps2-poi-position-marker-title
        :type: string
        :default: (empty)

        The title of the marker, which is shown as tooltip on mouse over.


..  _configuration-form-element-map-settings:

Map settings of the form element "Maps2PoiPosition"
===================================================

The element uses the following TypoScript settings of
`plugin.tx_maps2.settings`. `mapTile`, `mapTileAttribution` and
`googleMapsJavaScriptApiKey` are set by the site settings of the site sets
of maps2:

*   `mapTile` and `mapTileAttribution`: the tile server of OpenStreetMap
    and its attribution. Without a tile server, the tiles of
    `tile.openstreetmap.org` are used.
*   `googleMapsJavaScriptApiKey`: the API key for Google Maps. Without this
    setting, the API key of the extension settings is used.
*   `mapTypeId`, `zoomControl`, `mapTypeControl`, `scaleControl`,
    `streetViewControl`, `fullScreenControl`, `activateScrollWheel` and
    `styles`: the same settings as for the maps of the maps2 plugins.

See :ref:`typoScript`.


..  _configuration-form-element-consent:

Consent for requests to the map provider
========================================

If the extension setting `explicitAllowMapProviderRequests` is enabled and
the user has not agreed to requests to the map provider yet, the element
shows the same information as the maps2 plugins and a button to show the
map instead of the map.

The button submits the form to the current page of the form again and adds
the parameter `tx_maps2_maps2[mapProviderRequestsAllowedForMaps2]=1` to the
URI. The form state and all entered values are kept and the user stays on
the same page of the form.

The button disables the validation of the browser only. EXT:form validates
the submitted page as usual. So the user may see error messages after the
consent, for example of the validator `NotEmpty`, if there is no initial
position yet, or of other required fields on the same page.

..  tip::

    Put the element on a page of its own and set an initial position (see
    :ref:`developer-form-element-integration`). Then the page is valid
    already, when it is shown for the first time.

The JavaScript and style sheets of the map provider are loaded only after
the consent and only on pages of the form, which contain the element.


..  _configuration-form-element-output:

Summary page and emails
=======================

maps2 overrides the partial `SummaryPage` of EXT:form and shows the
position as text, for example:

..  code-block:: text

    Latitude: 48.663012, Longitude: 9.218945

The override is registered in the form setup of maps2 with the
`partialRootPaths` key `15` and, for the template variant `version2`, with
the key `16`.

The email templates of the email finishers of EXT:form are not overridden.
They render the value like the value of other elements with multiple
values: as list with the latitude and the longitude without labels.


..  _configuration-form-element-styling:

Styling of the form element "Maps2PoiPosition"
==============================================

The map and the consent information are wrapped in a `div` with the CSS
class `maps2-poi-position`. If the validation fails, the error class of the
form (`fieldProperties.errorClassAttribute`) is added to this `div`. With
the template variant `version2`, the surrounding container has the classes
`form-element form-element-maps2-poi-position mb-3`.


..  _configuration-form-element-limitations:

Limitations of the form element "Maps2PoiPosition"
==================================================

The element is not available in the backend form editor
    The form editor does not offer the element. Add the element to form
    definitions, which your extension or site package ships as YAML files.

An own SummaryPage partial with a higher key wins
    If your site package overrides `SummaryPage.html` with a higher
    `partialRootPaths` key than maps2, the position is shown as list again.
    Add a condition for the element type to your partial:

    ..  code-block:: html
        :caption: EXT:my_sitepackage/Resources/Private/Form/Partials/SummaryPage.html

        <f:if condition="{0: formValue.element.type} == {0: 'Maps2PoiPosition'}">
            <f:then>
                <f:render partial="Maps2PoiPosition/SummaryValue"
                          arguments="{position: formValue.value}"/>
            </f:then>
            <f:else>
                <!-- Rendering of all other values -->
            </f:else>
        </f:if>

    The partial `Maps2PoiPosition/SummaryValue` is found as long as the
    partial root path of maps2 (key `15`) is registered.

No geocoding and no storage
    The element shows and returns a position only. Geocoding an address and
    storing the position in a record are the job of your extension. See
    :ref:`developer-form-element-integration`.
