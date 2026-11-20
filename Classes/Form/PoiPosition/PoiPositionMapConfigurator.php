<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Form\PoiPosition;

use JWeiland\Maps2\Configuration\ExtConf;
use JWeiland\Maps2\Helper\MapHelper;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\TypoScript\FrontendTypoScript;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Form\Domain\Model\FormElements\FormElementInterface;

/**
 * Prepares the map of the form element "Maps2PoiPosition" for its template.
 * The template loads the assets of the map provider with the partial
 * "Maps2PoiPosition/LoadAssets", if requests to the map provider are allowed.
 * The map is rendered by the frontend JavaScript of maps2 in its
 * "editMarker" mode: the marker is draggable and a click into the map moves
 * the marker. Both update the hidden latitude/longitude fields.
 *
 * Map provider: property "mapProvider" ("gm" or "osm"). It is respected,
 * if both map providers are allowed in extension settings. Else the map
 * provider of the extension settings is used.
 */
readonly class PoiPositionMapConfigurator
{
    public const FORM_ELEMENT_TYPE = 'Maps2PoiPosition';

    public const RENDERING_OPTION = 'maps2PoiPosition';

    public const MAP_PROVIDER_GOOGLE_MAPS = 'gm';

    public const MAP_PROVIDER_OPEN_STREET_MAP = 'osm';

    private const DEFAULT_ZOOM = 15;

    private const MAX_ZOOM = 20;

    private const DEFAULT_HEIGHT = '400';

    private const HEIGHT_PATTERN = '/^\d+(\.\d+)?(px|em|rem|vh|%)?$/';

    private const MAP_WIDTH = '100%';

    /**
     * TypoScript settings of plugin.tx_maps2.settings, which are passed to the JavaScript of the map
     */
    private const TYPOSCRIPT_SETTINGS = [
        'activateScrollWheel',
        'fullScreenControl',
        'mapTile',
        'mapTileAttribution',
        'mapTypeControl',
        'mapTypeId',
        'scaleControl',
        'streetViewControl',
        'styles',
        'zoomControl',
    ];

    public function __construct(
        private ExtConf $extConf,
        private MapHelper $mapHelper,
        private TypoScriptService $typoScriptService,
    ) {}

    /**
     * @return array<string, mixed> Rendering option "maps2PoiPosition" of the form element
     */
    public function configure(
        FormElementInterface $element,
        mixed $value,
        ServerRequestInterface $request,
        int $currentPageIndex,
    ): array {
        $properties = $element->getProperties();
        $typoScriptSettings = $this->getTypoScriptSettings($request);
        $settings = $this->getMapSettings($properties, $typoScriptSettings);
        $position = $this->getValidPosition($value);

        return array_merge($this->getHiddenFieldValues($position), [
            'mapIdentifier' => $element->getUniqueIdentifier(),
            'mapProvider' => $settings['mapProvider'],
            'googleMapsJavaScriptApiKey' => $this->getGoogleMapsJavaScriptApiKey($typoScriptSettings),
            'isRequestToMapProviderAllowed' => $this->mapHelper->isRequestToMapProviderAllowed(),
            'currentPageIndex' => $currentPageIndex,
            'environment' => $this->encode($this->getEnvironment($element->getUniqueIdentifier(), $settings)),
            'pois' => $this->encode([$this->getPoi($position, $properties['markerTitle'] ?? '')]),
        ]);
    }

    public function getMapProvider(mixed $mapProvider): string
    {
        $mapProviders = [self::MAP_PROVIDER_GOOGLE_MAPS, self::MAP_PROVIDER_OPEN_STREET_MAP];
        $mapProvider = in_array($mapProvider, $mapProviders, true) ? $mapProvider : '';

        return $this->mapHelper->getMapProvider(['map_provider' => $mapProvider]);
    }

    public function getZoom(mixed $zoom): int
    {
        if (!is_numeric($zoom) || (int)$zoom < 0 || (int)$zoom > self::MAX_ZOOM) {
            return self::DEFAULT_ZOOM;
        }

        return (int)$zoom;
    }

    /**
     * Allows numbers (pixels) and numbers with the units px, em, rem, vh or %
     */
    public function getHeight(mixed $height): string
    {
        $height = is_scalar($height) ? trim((string)$height) : '';

        return preg_match(self::HEIGHT_PATTERN, $height) === 1 ? $height : self::DEFAULT_HEIGHT;
    }

    /**
     * The hidden fields stay empty without a valid position
     *
     * @return array{latitude: string, longitude: string}
     */
    private function getHiddenFieldValues(?PoiPosition $position): array
    {
        return $position?->toFormValue() ?? [PoiPosition::KEY_LATITUDE => '', PoiPosition::KEY_LONGITUDE => ''];
    }

    private function getValidPosition(mixed $value): ?PoiPosition
    {
        $position = PoiPosition::fromFormValue($value);

        return $position?->isValid() ? $position : null;
    }

    /**
     * @param array<string, mixed> $properties
     * @param array<string, mixed> $typoScriptSettings
     * @return array<string, mixed>
     */
    private function getMapSettings(array $properties, array $typoScriptSettings): array
    {
        $settings = array_intersect_key($typoScriptSettings, array_flip(self::TYPOSCRIPT_SETTINGS));
        $settings['mapProvider'] = $this->getMapProvider($properties['mapProvider'] ?? '');
        $settings['zoom'] = $this->getZoom($properties['zoom'] ?? null);
        $settings['mapHeight'] = $this->getHeight($properties['height'] ?? null);
        $settings['mapWidth'] = self::MAP_WIDTH;
        $settings['forceZoom'] = true;

        return $this->addMapTile($settings, $properties);
    }

    /**
     * Without the TypoScript setting "mapTile" (site set of OpenStreetMap not
     * included), the properties "defaultMapTile" and "defaultMapTileAttribution"
     * of the form element are used.
     *
     * @param array<string, mixed> $settings
     * @param array<string, mixed> $properties
     * @return array<string, mixed>
     */
    private function addMapTile(array $settings, array $properties): array
    {
        if (!is_string($settings['mapTile'] ?? null) || $settings['mapTile'] === '') {
            $settings['mapTile'] = $this->getStringProperty($properties, 'defaultMapTile');
            $settings['mapTileAttribution'] = $this->getStringProperty($properties, 'defaultMapTileAttribution');
        }

        // Leaflet does not support the placeholders "${x}" of the OpenStreetMap wiki
        $settings['mapTile'] = str_replace(
            ['${s}', '${x}', '${y}', '${z}'],
            ['{s}', '{x}', '{y}', '{z}'],
            $settings['mapTile'],
        );

        return $settings;
    }

    /**
     * @param array<string, mixed> $properties
     */
    private function getStringProperty(array $properties, string $name): string
    {
        return is_string($properties[$name] ?? null) ? $properties[$name] : '';
    }

    /**
     * @param array<string, mixed> $settings
     * @return array<string, mixed>
     */
    private function getEnvironment(string $mapIdentifier, array $settings): array
    {
        return [
            'settings' => $settings,
            'extConf' => [
                'defaultLatitude' => $this->extConf->getDefaultLatitude(),
                'defaultLongitude' => $this->extConf->getDefaultLongitude(),
            ],
            // The JavaScript finds the hidden fields by the CSS classes "latitude-<uid>" and "longitude-<uid>"
            'contentRecord' => [
                'uid' => $mapIdentifier,
            ],
            'ajaxUrl' => '',
        ];
    }

    /**
     * Without a valid position, the marker is shown at the default position of the extension settings
     *
     * @return array<string, mixed>
     */
    private function getPoi(?PoiPosition $position, mixed $markerTitle): array
    {
        return [
            'uid' => 0,
            'collectionType' => 'Point',
            'title' => is_scalar($markerTitle) ? (string)$markerTitle : '',
            'latitude' => $position instanceof PoiPosition ? $position->latitude : $this->extConf->getDefaultLatitude(),
            'longitude' => $position instanceof PoiPosition ? $position->longitude : $this->extConf->getDefaultLongitude(),
            'categories' => [],
        ];
    }

    /**
     * @param array<string, mixed> $typoScriptSettings
     */
    private function getGoogleMapsJavaScriptApiKey(array $typoScriptSettings): string
    {
        $apiKey = $typoScriptSettings['googleMapsJavaScriptApiKey'] ?? '';

        return is_string($apiKey) && $apiKey !== '' ? $apiKey : $this->extConf->getGoogleMapsJavaScriptApiKey();
    }

    /**
     * @return array<string, mixed>
     */
    private function getTypoScriptSettings(ServerRequestInterface $request): array
    {
        $frontendTypoScript = $request->getAttribute('frontend.typoscript');
        if (!$frontendTypoScript instanceof FrontendTypoScript) {
            return [];
        }

        try {
            $settings = $frontendTypoScript->getSetupArray()['plugin.']['tx_maps2.']['settings.'] ?? [];
        } catch (\RuntimeException) {
            // The setup is not available in cached frontend scope
            return [];
        }

        return is_array($settings) ? $this->typoScriptService->convertTypoScriptArrayToPlainArray($settings) : [];
    }

    /**
     * @param array<int|string, mixed> $data
     */
    private function encode(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
