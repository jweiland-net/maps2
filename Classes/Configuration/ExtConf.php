<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Configuration;

use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * This class will streamline the values from extension manager configuration
 */
#[Autoconfigure(constructor: 'create')]
final readonly class ExtConf
{
    private const EXT_KEY = 'maps2';

    private const DEFAULT_SETTINGS = [
        // general
        'mapProvider' => '',
        'defaultMapProvider' => 'gm',
        'defaultMapType' => 'Empty',
        'defaultCountry' => '',
        'defaultLatitude' => 0.0,
        'defaultLongitude' => 0.0,
        'defaultRadius' => 250,
        'explicitAllowMapProviderRequests' => false,
        'explicitAllowMapProviderRequestsBySessionOnly' => false,

        // Google Maps
        'googleMapsLibrary' => '',
        'googleMapsGeocodeUri' => 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s',
        'googleMapsJavaScriptApiKey' => '',
        'googleMapsGeocodeApiKey' => '',

        // Open Street Map
        'openStreetMapGeocodeUri' => 'https://nominatim.openstreetmap.org/search?q=%s&format=json&addressdetails=1',

        // Design/Color
        'strokeColor' => '#FF0000',
        'strokeOpacity' => 0.8,
        'strokeWeight' => 2,
        'fillColor' => '#FF0000',
        'fillOpacity' => 0.35,
        'markerIconWidth' => 0,
        'markerIconHeight' => 0,
        'markerIconAnchorPosX' => 0,
        'markerIconAnchorPosY' => 0,
    ];

    public function __construct(
        // general
        private string $mapProvider,
        private string $defaultMapProvider,
        private string $defaultMapType,
        private string $defaultCountry,
        private float $defaultLatitude,
        private float $defaultLongitude,
        private int $defaultRadius,
        private bool $explicitAllowMapProviderRequests,
        private bool $explicitAllowMapProviderRequestsBySessionOnly,

        // Google Maps
        private string $googleMapsLibrary,
        private string $googleMapsGeocodeUri,
        private string $googleMapsJavaScriptApiKey,
        private string $googleMapsGeocodeApiKey,

        // Open Street Map
        private string $openStreetMapGeocodeUri,

        // Design/Color
        private string $strokeColor,
        private float $strokeOpacity,
        private int $strokeWeight,
        private string $fillColor,
        private float $fillOpacity,
        private int $markerIconWidth,
        private int $markerIconHeight,
        private int $markerIconAnchorPosX,
        private int $markerIconAnchorPosY,
    ) {}

    public static function create(ExtensionConfiguration $extensionConfiguration): self
    {
        $extensionSettings = self::DEFAULT_SETTINGS;

        // Overwrite default extension settings with values from EXT_CONF
        try {
            $extensionSettings = array_merge(
                $extensionSettings,
                $extensionConfiguration->get(self::EXT_KEY)
            );
        } catch (ExtensionConfigurationExtensionNotConfiguredException|ExtensionConfigurationPathDoesNotExistException) {
        }

        return new self(
            // general
            mapProvider: (string)$extensionSettings['mapProvider'],
            defaultMapProvider: (string)$extensionSettings['defaultMapProvider'],
            defaultMapType: (string)$extensionSettings['defaultMapType'],
            defaultCountry: (string)$extensionSettings['defaultCountry'],
            defaultLatitude: (float)$extensionSettings['defaultLatitude'],
            defaultLongitude: (float)$extensionSettings['defaultLongitude'],
            defaultRadius: (int)$extensionSettings['defaultRadius'],
            explicitAllowMapProviderRequests: (bool)$extensionSettings['explicitAllowMapProviderRequests'],
            explicitAllowMapProviderRequestsBySessionOnly: (bool)$extensionSettings['explicitAllowMapProviderRequestsBySessionOnly'],

            // Google Maps
            googleMapsLibrary: (string)$extensionSettings['googleMapsLibrary'],
            googleMapsGeocodeUri: (string)$extensionSettings['googleMapsGeocodeUri'],
            googleMapsJavaScriptApiKey: (string)$extensionSettings['googleMapsJavaScriptApiKey'],
            googleMapsGeocodeApiKey: (string)$extensionSettings['googleMapsGeocodeApiKey'],

            // Open Street Map
            openStreetMapGeocodeUri: (string)$extensionSettings['openStreetMapGeocodeUri'],

            // Design/Color
            strokeColor: (string)$extensionSettings['strokeColor'],
            strokeOpacity: (float)$extensionSettings['strokeOpacity'],
            strokeWeight: (int)$extensionSettings['strokeWeight'],
            fillColor: (string)$extensionSettings['fillColor'],
            fillOpacity: (float)$extensionSettings['fillOpacity'],
            markerIconWidth: (int)$extensionSettings['markerIconWidth'],
            markerIconHeight: (int)$extensionSettings['markerIconHeight'],
            markerIconAnchorPosX: (int)$extensionSettings['markerIconAnchorPosX'],
            markerIconAnchorPosY: (int)$extensionSettings['markerIconAnchorPosY'],
        );
    }

    public function getMapProvider(): string
    {
        return $this->mapProvider;
    }

    public function getDefaultMapProvider(): string
    {
        return $this->defaultMapProvider;
    }

    public function getDefaultMapType(): string
    {
        return $this->defaultMapType;
    }

    public function getDefaultCountry(): string
    {
        return trim($this->defaultCountry);
    }

    public function getDefaultLatitude(): float
    {
        return $this->defaultLatitude;
    }

    public function getDefaultLongitude(): float
    {
        return $this->defaultLongitude;
    }

    public function getDefaultRadius(): int
    {
        return $this->defaultRadius;
    }

    public function getExplicitAllowMapProviderRequests(): bool
    {
        return $this->explicitAllowMapProviderRequests;
    }

    public function getExplicitAllowMapProviderRequestsBySessionOnly(): bool
    {
        return $this->explicitAllowMapProviderRequestsBySessionOnly;
    }

    public function getGoogleMapsLibrary(): string
    {
        $library = $this->googleMapsLibrary;

        // This was a bug. After upgrading to TYPO3 ~8.7 this value just contains "|" or is empty.
        // In that case fall back to default
        if (in_array($library, ['|', ''])) {
            $library = self::DEFAULT_SETTINGS['googleMapsLibrary'];
        }

        // insert ApiKey
        $library = str_replace('|', $this->getGoogleMapsJavaScriptApiKey(), $library);
        // $parts: 0 = full string; 1 = s or empty; 2 = needed url
        if (preg_match('#^http(s)?://(.*)$#i', $library, $parts)) {
            return 'https://' . $parts[2];
        }

        return self::DEFAULT_SETTINGS['googleMapsLibrary'];
    }

    public function getGoogleMapsGeocodeUri(): string
    {
        return trim($this->googleMapsGeocodeUri);
    }

    public function getGoogleMapsJavaScriptApiKey(): string
    {
        return trim($this->googleMapsJavaScriptApiKey);
    }

    public function getGoogleMapsGeocodeApiKey(): string
    {
        return trim($this->googleMapsGeocodeApiKey);
    }

    public function getOpenStreetMapGeocodeUri(): string
    {
        return trim($this->openStreetMapGeocodeUri);
    }

    public function getStrokeColor(): string
    {
        return $this->strokeColor;
    }

    public function getStrokeOpacity(): float
    {
        return $this->strokeOpacity;
    }

    public function getStrokeWeight(): int
    {
        return $this->strokeWeight;
    }

    public function getFillColor(): string
    {
        return $this->fillColor;
    }

    public function getFillOpacity(): float
    {
        return $this->fillOpacity;
    }

    public function getMarkerIconWidth(): int
    {
        return $this->markerIconWidth;
    }

    public function getMarkerIconHeight(): int
    {
        return $this->markerIconHeight;
    }

    public function getMarkerIconAnchorPosX(): int
    {
        return $this->markerIconAnchorPosX;
    }

    public function getMarkerIconAnchorPosY(): int
    {
        return $this->markerIconAnchorPosY;
    }
}
