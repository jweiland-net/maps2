:navigation-title: Form element

..  include:: /Includes.rst.txt
..  _developer-form-element:

======================================================
Integrate the form element "Maps2PoiPosition" in code
======================================================

..  versionadded:: 12.3.0

    maps2 provides the form element `Maps2PoiPosition` for forms based on
    the TYPO3 form framework (EXT:form).

The form element `Maps2PoiPosition` shows a map with a draggable marker,
so frontend users can correct a position, which your extension has found
by geocoding an address. The chapter :ref:`configuration-form-element`
describes the requirements, the properties and the output of the element.

This chapter is for developers of extensions, which use the element. It
describes the value of the element, its validation and a complete example:
a form, which geocodes the entered address and stores the corrected
position.

..  contents::
    :local:


..  _developer-form-element-responsibilities:

Tasks of maps2 and of your extension
====================================

maps2 renders the map, writes the position of the marker into the form and
validates the submitted position.

Your extension

*   ships the form definition with the element,
*   sets the initial position, for example after geocoding an address,
*   stores the submitted position, for example in a finisher.


..  _developer-form-element-value:

Value of the form element "Maps2PoiPosition"
============================================

The value of the element is an array with the keys `latitude` and
`longitude`. Both values are numeric strings:

..  code-block:: php
    :caption: Value of the form element "Maps2PoiPosition"

    [
        'latitude' => '48.663012',
        'longitude' => '9.218945',
    ]

The template of the element renders two hidden fields, which the
JavaScript of maps2 updates with the position of the marker, rounded to 6
decimals:

..  code-block:: text

    tx_form_formframework[<form identifier>][<element identifier>][latitude]
    tx_form_formframework[<form identifier>][<element identifier>][longitude]

After the submit, maps2 normalizes the value in an `afterSubmit` hook of
EXT:form:

*   Both values are trimmed. Other keys than `latitude` and `longitude` are
    removed.
*   If both values are empty (the marker was not placed), the value is
    `null`. So the validator `NotEmpty` applies.

The class :php:`\JWeiland\Maps2\Form\PoiPosition\PoiPosition` converts
between a position and the value of the element:

..  code-block:: php
    :caption: Convert a position from and to the value of the element

    use JWeiland\Maps2\Form\PoiPosition\PoiPosition;

    // Returns null, if the value is not an array with numeric coordinates
    $position = PoiPosition::fromFormValue($formValues['position'] ?? null);
    $latitude = $position?->latitude;

    // ['latitude' => '48.663012', 'longitude' => '9.218945']
    $value = (new PoiPosition(48.6630123, 9.2189451))->toFormValue();


..  _developer-form-element-initial-position:

Initial position of the map
===========================

The map shows the value of the element in the following order:

#.  The value in the form state, for example the submitted value or a value,
    which a hook has written into the form state.
#.  The default value of the element: `defaultValue` in the form definition
    or a value set with :php:`setDefaultValue()`.

Both use the array format of the value. Only a valid position (numeric,
latitude -90 to 90, longitude -180 to 180) is used.

Without a valid position, the marker is shown at the default position of
the :ref:`extension settings <extensionSettings>` (`defaultLatitude`,
`defaultLongitude`), but the hidden fields stay empty. So the element has no
value, until the user places the marker.


..  _developer-form-element-validation:

Validation of the form element "Maps2PoiPosition"
=================================================

maps2 adds the validator `Maps2PoiPosition` to each element automatically.
It accepts empty values. Add the validator `NotEmpty` to require a position.

A value, which is not empty, has to fulfill these rules:

..  t3-field-list-table::
    :header-rows: 1

    -   :Code: Error code
        :Rule: Rule
        :Message: Message

    -   :Code: `1763380001`
        :Rule: Latitude and longitude are numeric.
        :Message: Please place the marker on the map.

    -   :Code: `1763380002`
        :Rule: The latitude is between -90 and 90.
        :Message: The latitude must be between -90 and 90.

    -   :Code: `1763380003`
        :Rule: The longitude is between -180 and 180.
        :Message: The longitude must be between -180 and 180.

The validator translates the messages with the keys
`validator.poiPosition.notNumeric`, `validator.poiPosition.latitudeOutOfRange`
and `validator.poiPosition.longitudeOutOfRange` of the language file of
maps2. The error codes are available as constants of
:php:`\JWeiland\Maps2\Form\Validator\PoiPositionValidator`.

Override a message for your forms with the error code in a translation
file of EXT:form, for example `validation.error.1763380001` (all forms) or
`<form identifier>.validation.error.<element identifier>.1763380001`
(a single element). Register the translation file with
`renderingOptions.translation.translationFiles` of the form.


..  _developer-form-element-templates:

Templates of the form element "Maps2PoiPosition"
================================================

All paths of templates, JavaScript, style sheets and URLs are defined in
Fluid templates and in the form setup, not in PHP. The partials are in
`EXT:maps2/Resources/Private/FormFramework/Partials/` (partial root path key
`15`, key `16` for the `SummaryPage` of the template variant `version2`):

`Maps2PoiPosition`
    The element: hidden fields with the position and the map or the
    consent information.

`Maps2PoiPosition/LoadAssets`
    JavaScript and style sheets of the map provider. See
    :ref:`configuration-form-element-assets`.

`Maps2PoiPosition/Overlay`
    Consent information with the button to show the map.

`Maps2PoiPosition/SummaryValue`
    The position as text on the summary page.

The hook :php:`\JWeiland\Maps2\Hook\Form\PoiPositionMapHook` prepares the
data of the map in the rendering option `maps2PoiPosition` of the element:
`latitude`, `longitude` (values of the hidden fields), `mapIdentifier`,
`mapProvider`, `googleMapsJavaScriptApiKey`,
`isRequestToMapProviderAllowed`, `currentPageIndex`, `environment` and
`pois` (JSON for the JavaScript of maps2).


..  _developer-form-element-integration:

Integrate the form element "Maps2PoiPosition" step by step
==========================================================

The following example uses a form with two pages: the user enters the
address on the first page. When the page is submitted, a hook geocodes the
address. The second page shows the map with the found position, where the
user can correct it. After the submit, a finisher stores the position as
POI collection and another finisher stores the record with a relation to
the POI collection.

Replace `MyVendor`, `MyExtension` and `my_extension` with the names of
your extension.


..  _developer-form-element-integration-form-definition:

Step 1: Create the form definition
----------------------------------

..  code-block:: yaml
    :caption: EXT:my_extension/Resources/Private/Forms/Company.form.yaml

    type: Form
    identifier: company
    label: 'Company'
    prototypeName: standard
    renderables:
      - type: Page
        identifier: page-address
        label: 'Address'
        renderingOptions:
          nextButtonLabel: 'Next step: position on the map'
        renderables:
          - type: Text
            identifier: company
            label: 'Company'
            validators:
              - identifier: NotEmpty
          - type: Text
            identifier: street
            label: 'Street and house number'
            validators:
              - identifier: NotEmpty
          - type: Text
            identifier: zip
            label: 'ZIP code'
            validators:
              - identifier: NotEmpty
          - type: Text
            identifier: city
            label: 'City'
            validators:
              - identifier: NotEmpty
      - type: Page
        identifier: page-map
        label: 'Position on the map'
        renderables:
          - type: Maps2PoiPosition
            identifier: position
            label: 'Position on the map'
            properties:
              markerTitle: 'Your company'
              elementDescription: 'Move the marker, if the position is not correct.'
            validators:
              - identifier: NotEmpty
      - type: SummaryPage
        identifier: page-summary
        label: 'Summary'
    finishers:
      - identifier: SavePosition
        options:
          storagePid: 12
          title: '{company}'
      - identifier: SaveToDatabase
        options:
          table: tx_myextension_domain_model_company
          mode: insert
          elements:
            company:
              mapOnDatabaseColumn: title
            street:
              mapOnDatabaseColumn: street
            zip:
              mapOnDatabaseColumn: zip
            city:
              mapOnDatabaseColumn: city
          databaseColumnMappings:
            pid:
              value: 12
            tx_maps2_uid:
              value: '{SavePosition.poiCollectionUid}'

The column `tx_maps2_uid` is the relation to the POI collection, which you
can add to your table with the :ref:`Maps2Registry <developer-maps2registry>`.


..  _developer-form-element-integration-form-setup:

Step 2: Register the form and the finisher
------------------------------------------

Allow EXT:form to load forms of your extension and register the finisher
`SavePosition`:

..  code-block:: yaml
    :caption: EXT:my_extension/Configuration/Form/FormSetup.yaml

    TYPO3:
      CMS:
        Form:
          persistenceManager:
            allowedExtensionPaths:
              1763390000: 'EXT:my_extension/Resources/Private/Forms/'
          prototypes:
            standard:
              finishersDefinition:
                SavePosition:
                  implementationClassName: 'MyVendor\MyExtension\Form\Finisher\SavePositionFinisher'

Register the form setup in the site set of your extension, which depends on
the site set of maps2:

..  code-block:: yaml
    :caption: EXT:my_extension/Configuration/Sets/MyExtension/config.yaml

    name: my-vendor/my-extension
    label: 'My extension'
    dependencies:
      - typo3/form
      - jweiland/maps2-default

..  code-block:: typoscript
    :caption: EXT:my_extension/Configuration/Sets/MyExtension/setup.typoscript

    plugin.tx_form.settings.yamlConfigurations {
        1763390000 = EXT:my_extension/Configuration/Form/FormSetup.yaml
    }


..  _developer-form-element-integration-geocoding:

Step 3: Geocode the address in a hook
-------------------------------------

EXT:form calls the hooks `afterSubmit` for the submitted page first and then
for each element of this page. The following hook reacts to the page with
the address, geocodes the address with the
:php:`\JWeiland\Maps2\Service\GeoCodeService` of maps2 and writes the found
position into the form state. Then the map on the next page shows this
position.

..  code-block:: php
    :caption: EXT:my_extension/Classes/Hook/Form/GeocodeAddressHook.php

    <?php

    declare(strict_types=1);

    namespace MyVendor\MyExtension\Hook\Form;

    use JWeiland\Maps2\Domain\Model\Position;
    use JWeiland\Maps2\Form\PoiPosition\PoiPosition;
    use JWeiland\Maps2\Service\GeoCodeService;
    use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
    use TYPO3\CMS\Form\Domain\Model\FormElements\Page;
    use TYPO3\CMS\Form\Domain\Model\Renderable\RenderableInterface;
    use TYPO3\CMS\Form\Domain\Runtime\FormRuntime;

    /**
     * Geocodes the address, when the page with the address is submitted,
     * and sets the found position as value of the element "position".
     */
    #[Autoconfigure(public: true)]
    final readonly class GeocodeAddressHook
    {
        private const ADDRESS_PAGE = 'page-address';

        private const ADDRESS_ELEMENTS = ['street', 'zip', 'city'];

        private const POSITION_ELEMENT = 'position';

        /**
         * Key in the form state with the last geocoded address
         */
        private const GEOCODED_ADDRESS = '__myExtensionGeocodedAddress';

        public function __construct(
            private GeoCodeService $geoCodeService,
        ) {}

        /**
         * @param array<string, mixed> $requestArguments
         */
        public function afterSubmit(
            FormRuntime $formRuntime,
            RenderableInterface $renderable,
            mixed $elementValue = null,
            array $requestArguments = [],
        ): mixed {
            $formState = $formRuntime->getFormState();
            if (!$renderable instanceof Page
                || $renderable->getIdentifier() !== self::ADDRESS_PAGE
                || $formState === null
            ) {
                return $elementValue;
            }

            // Geocode an unchanged address not again. So a corrected
            // position is kept, if the user goes back to the address.
            $address = $this->buildAddress($requestArguments);
            if ($address === ''
                || $address === $formState->getFormValue(self::GEOCODED_ADDRESS)
            ) {
                return $elementValue;
            }

            $position = $this->geoCodeService
                ->getFirstFoundPositionByAddress($address);
            if ($position instanceof Position) {
                $poiPosition = new PoiPosition(
                    $position->getLatitude(),
                    $position->getLongitude(),
                );
                $formState->setFormValue(
                    self::POSITION_ELEMENT,
                    $poiPosition->toFormValue(),
                );
                $formState->setFormValue(self::GEOCODED_ADDRESS, $address);
            }

            return $elementValue;
        }

        /**
         * @param array<string, mixed> $requestArguments
         */
        private function buildAddress(array $requestArguments): string
        {
            $parts = [];
            foreach (self::ADDRESS_ELEMENTS as $identifier) {
                $value = $requestArguments[$identifier] ?? '';
                $parts[] = is_string($value) ? trim($value) : '';
            }

            return trim(sprintf('%s, %s %s', ...$parts), ' ,');
        }
    }

EXT:form creates hooks with :php:`GeneralUtility::makeInstance()`. The
attribute :php:`#[Autoconfigure(public: true)]` makes the hook available
in the dependency injection container, so its constructor arguments are
injected.

Register the hook:

..  code-block:: php
    :caption: EXT:my_extension/ext_localconf.php

    <?php

    declare(strict_types=1);

    use MyVendor\MyExtension\Hook\Form\GeocodeAddressHook;

    defined('TYPO3') or die();

    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['ext/form']['afterSubmit'][1763390001]
        = GeocodeAddressHook::class;

If the address can not be geocoded, the map shows the previous position or
the default position of the extension settings and the user has to place
the marker. Your hook can add an error to an element of the address page
instead, so the user stays on the address page:

..  code-block:: php
    :caption: Show an error at the element "street"

    use TYPO3\CMS\Extbase\Error\Error;

    $formRuntime->getFormDefinition()
        ->getProcessingRule('street')
        ->getProcessingMessages()
        ->addError(new Error('The address was not found.', 1763390002));

The geocoding uses the map provider and the API keys of the
:ref:`extension settings <extensionSettings>`.


..  _developer-form-element-integration-default-value:

Step 4: Show the stored position of an existing record
------------------------------------------------------

If the form edits an existing record, set the stored position as default
value of the element. Use a hook, which has access to the request, for
example `afterInitializeCurrentPage`:

..  code-block:: php
    :caption: Set the stored position as default value

    use JWeiland\Maps2\Form\PoiPosition\PoiPosition;

    // $latitude and $longitude of the POI collection of the edited record
    $position = new PoiPosition($latitude, $longitude);
    $formRuntime->getFormDefinition()
        ->getElementByIdentifier('position')
        ?->setDefaultValue($position->toFormValue());

A value in the form state wins over the default value. So the geocoding
hook of step 3 replaces the stored position, as soon as the address page is
submitted. Compare the submitted address with the stored address of the
record and skip the geocoding, if the address was not changed.


..  _developer-form-element-integration-finisher:

Step 5: Store the position in a finisher
----------------------------------------

The following finisher creates a POI collection of type `Point` with the
submitted position. It provides the UID of the new record as finisher
variable `SavePosition.poiCollectionUid` for the following finishers, see
step 1.

..  code-block:: php
    :caption: EXT:my_extension/Classes/Form/Finisher/SavePositionFinisher.php

    <?php

    declare(strict_types=1);

    namespace MyVendor\MyExtension\Form\Finisher;

    use JWeiland\Maps2\Form\PoiPosition\PoiPosition;
    use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
    use TYPO3\CMS\Core\Database\ConnectionPool;
    use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

    /**
     * Stores the position of the element "Maps2PoiPosition" as new POI
     * collection of type "Point".
     */
    #[Autoconfigure(public: true, shared: false)]
    class SavePositionFinisher extends AbstractFinisher
    {
        private const TABLE = 'tx_maps2_domain_model_poicollection';

        protected $defaultOptions = [
            'elementIdentifier' => 'position',
            'storagePid' => 0,
            'title' => '',
        ];

        public function __construct(
            private readonly ConnectionPool $connectionPool,
        ) {}

        protected function executeInternal(): ?string
        {
            $formValues = $this->finisherContext->getFormValues();
            $position = PoiPosition::fromFormValue(
                $formValues[(string)$this->parseOption('elementIdentifier')] ?? null,
            );
            if ($position === null) {
                return null;
            }

            $connection = $this->connectionPool->getConnectionForTable(self::TABLE);
            $connection->insert(self::TABLE, [
                'pid' => (int)$this->parseOption('storagePid'),
                'tstamp' => time(),
                'crdate' => time(),
                'collection_type' => 'Point',
                'title' => (string)$this->parseOption('title'),
                'latitude' => $position->latitude,
                'longitude' => $position->longitude,
            ]);

            $this->finisherContext->getFinisherVariableProvider()->add(
                $this->shortFinisherIdentifier,
                'poiCollectionUid',
                (int)$connection->lastInsertId(),
            );

            return null;
        }
    }

To edit an existing record, update the POI collection, which is related to
the record, instead of creating a new one.

..  hint::

    The validator `Maps2PoiPosition` has checked the position already.
    :php:`PoiPosition::fromFormValue()` returns `null`, if the element is
    optional and the user has not placed the marker.


..  _developer-form-element-integration-render:

Step 6: Show the form
---------------------

Add the content element :guilabel:`Form` to a page and select the form
:guilabel:`Company`, or render the form in a Fluid template of your
extension:

..  code-block:: html
    :caption: EXT:my_extension/Resources/Private/Templates/Company/New.html

    <html xmlns:formvh="http://typo3.org/ns/TYPO3/CMS/Form/ViewHelpers"
          data-namespace-typo3-fluid="true">

    <formvh:render
        persistenceIdentifier="EXT:my_extension/Resources/Private/Forms/Company.form.yaml"/>

    </html>

Include the site set of your extension in the site, so the form setup of
your extension and of maps2 is loaded.
