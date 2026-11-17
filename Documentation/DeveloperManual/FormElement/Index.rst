..  include:: /Includes.rst.txt


..  _developer-form-element:

=====================================
Form element "Maps2PoiPosition"
=====================================

The form element `Maps2PoiPosition` adds a map with a draggable marker
to forms based on EXT:form. Frontend users can correct a position, which
was found by geocoding an address. Dragging the marker or clicking into the
map moves the marker and updates the position.

The element does not geocode and does not store anything. Your extension
sets the initial position (e.g. in a form hook after geocoding) and stores
the submitted position (e.g. in a finisher).

..  contents::
    :local:


Requirements
============

*   EXT:form (`typo3/cms-form`) is installed.
*   The site set "Maps2 - Default Set" (`jweiland/maps2-default`) is
    included, directly or as dependency of the sets
    `jweiland/maps2-googlemaps` or `jweiland/maps2-openstreetmap`.
    It registers the form setup
    `EXT:maps2/Configuration/Form/FormSetup.yaml` with
    `plugin.tx_form.settings.yamlConfigurations.1763380200`.

The element is not available in the backend form editor. Use it in form
definitions, which are shipped as YAML files by your extension.


Value
=====

The value of the element is an array with the latitude and longitude as
numeric strings:

..  code-block:: php

    [
        'latitude' => '48.663012',
        'longitude' => '9.218945',
    ]

The template renders two hidden fields
`tx_form_formframework[<form identifier>][<element identifier>][latitude]`
and `...[longitude]`. The JavaScript of maps2 writes the position of the
marker with 6 decimals into these fields.

*   Submitted values are trimmed and reduced to the keys `latitude` and
    `longitude`.
*   Empty coordinates (marker not placed) result in `null`.
*   The validator `Maps2PoiPosition` is added to each element automatically.
    It requires numeric values, a latitude between -90 and 90 and a
    longitude between -180 and 180. Error codes: `1763380001` (not numeric),
    `1763380002` (latitude out of range), `1763380003` (longitude out of
    range).
*   Add the validator `NotEmpty` to require a position.


Initial position
================

The map shows the value of the element in the form state (e.g. the
submitted value or a value, which a hook writes into the form state) or
else the default value of the element. Both have the same array format.

Without a valid position, the marker is shown at the default latitude and
longitude of the extension settings of maps2, but the hidden fields stay
empty. So a required element is invalid until the user places the marker.


Properties
==========

..  confval:: mapProvider
    :name: form-element-mapProvider
    :type: string
    :Default: '' (map provider of the extension settings)

    `gm` (Google Maps) or `osm` (OpenStreetMap). Respected only, if the
    extension setting `mapProvider` is `both`. Else the map provider of the
    extension settings is used. An empty or invalid value uses
    `defaultMapProvider`.

..  confval:: zoom
    :name: form-element-zoom
    :type: int
    :Default: 15

    Zoom level of the map (0 to 20).

..  confval:: height
    :name: form-element-height
    :type: int|string
    :Default: 400

    Height of the map. A number (pixels) or a number with one of the units
    `px`, `em`, `rem`, `vh` or `%`. The width of the map is always `100%`.

..  confval:: markerTitle
    :name: form-element-markerTitle
    :type: string
    :Default: ''

    Title of the marker, which is shown on mouse over.

Other properties like `elementDescription` work as for other elements.
Additional properties of your own extension are ignored by maps2.

The TypoScript settings `plugin.tx_maps2.settings` of the site sets of
maps2 are used for the map: `mapTile` and `mapTileAttribution`
(OpenStreetMap), `googleMapsJavaScriptApiKey` (Google Maps, fallback: the
extension setting), `mapTypeId`, `zoomControl`, `mapTypeControl`,
`scaleControl`, `streetViewControl`, `fullScreenControl`,
`activateScrollWheel` and `styles`.


Consent for requests to the map provider
========================================

If the extension setting `explicitAllowMapProviderRequests` is enabled and
the user has not agreed yet, the element shows the same information as the
overlay of the maps2 plugins and a button to show the map. The button
submits the form to its current page again. Its URI contains the parameter
`tx_maps2_maps2[mapProviderRequestsAllowedForMaps2]=1`. So the form state
and the entered values are kept. The hidden fields with the position are
rendered in both cases.

The assets of the map provider are loaded on form pages with the map only
and only after consent.


Summary page
============

maps2 overrides the partial `SummaryPage` of EXT:form (partial root path
key `15`, and `16` for the template variant `version2`) and renders the
position as text: "Latitude: 48.663012, Longitude: 9.218945". If your site
package overrides `SummaryPage.html` with a higher key, add a condition for
the element type `Maps2PoiPosition` and render the partial
`Maps2PoiPosition/SummaryValue` with the argument `position`.

The email templates of EXT:form render the value as a list of the two
coordinates.


Integration example
===================

Form definition with a page for the address and a page with the map:

..  code-block:: yaml
    :caption: EXT:my_extension/Configuration/Form/Company.form.yaml

    identifier: company
    type: Form
    prototypeName: standard
    label: 'Company'
    renderables:
      - type: Page
        identifier: page-address
        label: 'Address'
        renderables:
          - type: Text
            identifier: street
            label: 'Street'
          - type: Text
            identifier: city
            label: 'City'
      - type: Page
        identifier: page-map
        label: 'Map'
        renderables:
          - type: Maps2PoiPosition
            identifier: position
            label: 'Position on the map'
            properties:
              zoom: 15
              height: 400
              markerTitle: 'Your company'
              elementDescription: 'Move the marker, if the position is not correct.'
            validators:
              - identifier: NotEmpty
    finishers:
      - identifier: SaveCompanyFinisher

Hook, which geocodes the address after the page with the address was
submitted and writes the position into the form state:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Hook/Form/GeocodeAddressHook.php

    use JWeiland\Maps2\Domain\Model\Position;
    use JWeiland\Maps2\Service\GeoCodeService;
    use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
    use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
    use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

    final readonly class GeocodeAddressHook
    {
        public function __construct(
            private GeoCodeService $geoCodeService,
        ) {}

        public function afterSubmit(
            FormRuntime $formRuntime,
            RenderableInterface $renderable,
            mixed $elementValue,
            array $requestArguments = [],
        ): mixed {
            if (!$renderable instanceof Page || $renderable->getIdentifier() !== 'page-address') {
                return $elementValue;
            }

            $address = trim(($requestArguments['street'] ?? '') . ', ' . ($requestArguments['city'] ?? ''), ' ,');
            $position = $this->geoCodeService->getFirstFoundPositionByAddress($address);
            if ($position instanceof Position) {
                $formRuntime->getFormState()?->setFormValue('position', [
                    'latitude' => number_format($position->getLatitude(), 6, '.', ''),
                    'longitude' => number_format($position->getLongitude(), 6, '.', ''),
                ]);
            }

            return $elementValue;
        }
    }

..  code-block:: php
    :caption: EXT:my_extension/ext_localconf.php

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][1763390000]
        = \MyVendor\MyExtension\Hook\Form\GeocodeAddressHook::class;

To edit an existing record, set the stored position as default value, e.g.
in a hook `afterBuildingFinished`:

..  code-block:: php

    $element->setDefaultValue([
        'latitude' => (string)$poiCollection['latitude'],
        'longitude' => (string)$poiCollection['longitude'],
    ]);

Finisher, which stores the position in the table
`tx_maps2_domain_model_poicollection`:

..  code-block:: php
    :caption: EXT:my_extension/Classes/Domain/Finisher/SaveCompanyFinisher.php

    use TYPO3\CMS\Core\Database\ConnectionPool;
    use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

    class SaveCompanyFinisher extends AbstractFinisher
    {
        public function __construct(
            private readonly ConnectionPool $connectionPool,
        ) {}

        protected function executeInternal(): ?string
        {
            $position = $this->finisherContext->getFormValues()['position'] ?? null;
            if (!is_array($position)) {
                return null;
            }

            $this->connectionPool
                ->getConnectionForTable('tx_maps2_domain_model_poicollection')
                ->update(
                    'tx_maps2_domain_model_poicollection',
                    [
                        'latitude' => (float)$position['latitude'],
                        'longitude' => (float)$position['longitude'],
                    ],
                    ['uid' => $this->getPoiCollectionUid()],
                );

            return null;
        }
    }

..  hint::

    Use `DataHandler` instead, if the record has to be created or
    history and hooks of TYPO3 are needed.
