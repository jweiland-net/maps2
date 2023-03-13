<?php

declare(strict_types=1);

/*
 * This file is part of the package jweiland/maps2.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace JWeiland\Maps2\Configuration;

use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class will streamline the values from extension manager configuration
 */
class ExtConf implements SingletonInterface
{
    // general
    protected string $mapProvider = '';
    protected string $defaultMapProvider = '';
    protected string $defaultMapType = 'Empty';
    protected string $defaultCountry = '';
    protected float $defaultLatitude = 0.0;
    protected float $defaultLongitude = 0.0;
    protected int $defaultRadius = 0;
    protected bool $explicitAllowMapProviderRequests = false;
    protected bool $explicitAllowMapProviderRequestsBySessionOnly = false;
    protected string $infoWindowContentTemplatePath = '';

    // Google Maps
    protected string $googleMapsLibrary = '';
    protected string $googleMapsGeocodeUri = '';
    protected string $googleMapsJavaScriptApiKey = '';
    protected string $googleMapsGeocodeApiKey = '';

    // Open Street Map
    protected string $openStreetMapGeocodeUri = '';

    // Design/Color
    protected string $strokeColor = '';
    protected float $strokeOpacity = 0.0;
    protected int $strokeWeight = 0;
    protected string $fillColor = '';
    protected float $fillOpacity = 0.0;
    protected int $markerIconWidth = 0;
    protected int $markerIconHeight = 0;
    protected int $markerIconAnchorPosX = 0;
    protected int $markerIconAnchorPosY = 0;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        try {
            $extConf = $extensionConfiguration->get('maps2');
            if (!is_array($extConf)) {
                return;
            }

            if (empty($extConf)) {
                return;
            }

            // call setter method foreach configuration entry
            foreach ($extConf as $key => $value) {
                $methodName = 'set' . ucfirst($key);
                if (method_exists($this, $methodName)) {
                    $this->$methodName($value);
                }
            }
        } catch (ExtensionConfigurationExtensionNotConfiguredException | ExtensionConfigurationPathDoesNotExistException $e) {
        }
    }

    public function getMapProvider(): string
    {
        if (empty($this->mapProvider)) {
            $this->mapProvider = 'both';
        }

        return $this->mapProvider;
    }

    public function setMapProvider(string $mapProvider): void
    {
        $this->mapProvider = $mapProvider;
    }

    public function getDefaultMapProvider(): string
    {
        if (empty($this->defaultMapProvider)) {
            $this->defaultMapProvider = 'gm';
        }

        return $this->defaultMapProvider;
    }

    public function setDefaultMapProvider(string $defaultMapProvider): void
    {
        $this->defaultMapProvider = $defaultMapProvider;
    }

    public function getDefaultMapType(): string
    {
        if (empty($this->defaultMapType)) {
            $this->defaultMapType = 'Empty';
        }

        return $this->defaultMapType;
    }

    public function setDefaultMapType(string $defaultMapType): void
    {
        $this->defaultMapType = $defaultMapType;
    }

    public function getDefaultCountry(): string
    {
        return $this->defaultCountry;
    }

    public function setDefaultCountry(string $defaultCountry): void
    {
        $this->defaultCountry = trim($defaultCountry);
    }

    public function getDefaultLatitude(): float
    {
        if (empty($this->defaultLatitude)) {
            return 0.00;
        }

        return $this->defaultLatitude;
    }

    public function setDefaultLatitude($defaultLatitude): void
    {
        $this->defaultLatitude = (float)$defaultLatitude;
    }

    public function getDefaultLongitude(): float
    {
        if (empty($this->defaultLongitude)) {
            return 0.00;
        }

        return $this->defaultLongitude;
    }

    public function setDefaultLongitude($defaultLongitude): void
    {
        $this->defaultLongitude = (float)$defaultLongitude;
    }

    public function getDefaultRadius(): int
    {
        if (empty($this->defaultRadius)) {
            return 250;
        }

        return $this->defaultRadius;
    }

    public function setDefaultRadius($defaultRadius): void
    {
        $this->defaultRadius = (int)$defaultRadius;
    }

    public function getExplicitAllowMapProviderRequests(): bool
    {
        return $this->explicitAllowMapProviderRequests;
    }

    public function setExplicitAllowMapProviderRequests($explicitAllowMapProviderRequests): void
    {
        $this->explicitAllowMapProviderRequests = (bool)$explicitAllowMapProviderRequests;
    }

    public function getExplicitAllowMapProviderRequestsBySessionOnly(): bool
    {
        return $this->explicitAllowMapProviderRequestsBySessionOnly;
    }

    public function setExplicitAllowMapProviderRequestsBySessionOnly($explicitAllowMapProviderRequestsBySessionOnly): void
    {
        $this->explicitAllowMapProviderRequestsBySessionOnly = (bool)$explicitAllowMapProviderRequestsBySessionOnly;
    }

    public function getInfoWindowContentTemplatePath(): string
    {
        if (empty($this->infoWindowContentTemplatePath)) {
            $this->infoWindowContentTemplatePath = 'EXT:maps2/Resources/Private/Templates/InfoWindowContent.html';
        }

        return $this->infoWindowContentTemplatePath;
    }

    public function setInfoWindowContentTemplatePath(string $infoWindowContentTemplatePath): void
    {
        $this->infoWindowContentTemplatePath = $infoWindowContentTemplatePath;
    }

    public function getGoogleMapsLibrary(): string
    {
        $library = $this->googleMapsLibrary;
        if (in_array($this->googleMapsLibrary, ['|', ''])) {
            $library = 'https://maps.googleapis.com/maps/api/js?key=|&libraries=places';
        }

        if (!empty($library)) {
            // insert ApiKey
            $library = str_replace('|', $this->getGoogleMapsJavaScriptApiKey(), $library);
            // $parts: 0 = full string; 1 = s or empty; 2 = needed url
            if (preg_match('#^http(s)?://(.*)$#i', $library, $parts)) {
                return 'https://' . $parts[2];
            }
        }

        return '';
    }

    public function setGoogleMapsLibrary(string $googleMapsLibrary): void
    {
        $this->googleMapsLibrary = trim($googleMapsLibrary);
    }

    public function getGoogleMapsGeocodeUri(): string
    {
        if (empty($this->googleMapsGeocodeUri)) {
            $this->googleMapsGeocodeUri = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s';
        }

        return $this->googleMapsGeocodeUri;
    }

    public function setGoogleMapsGeocodeUri(string $googleMapsGeocodeUri): void
    {
        $this->googleMapsGeocodeUri = trim($googleMapsGeocodeUri);
    }

    public function getGoogleMapsJavaScriptApiKey(): string
    {
        return $this->googleMapsJavaScriptApiKey;
    }

    public function setGoogleMapsJavaScriptApiKey(string $googleMapsJavaScriptApiKey): void
    {
        $this->googleMapsJavaScriptApiKey = trim($googleMapsJavaScriptApiKey);
    }

    public function getGoogleMapsGeocodeApiKey(): string
    {
        return $this->googleMapsGeocodeApiKey;
    }

    public function setGoogleMapsGeocodeApiKey(string $googleMapsGeocodeApiKey): void
    {
        $this->googleMapsGeocodeApiKey = trim($googleMapsGeocodeApiKey);
    }

    public function getOpenStreetMapGeocodeUri(): string
    {
        if (empty($this->openStreetMapGeocodeUri)) {
            $this->openStreetMapGeocodeUri = 'https://nominatim.openstreetmap.org/search/%s?format=json&addressdetails=1';
        }

        return $this->openStreetMapGeocodeUri;
    }

    public function setOpenStreetMapGeocodeUri(string $openStreetMapGeocodeUri): void
    {
        $this->openStreetMapGeocodeUri = trim($openStreetMapGeocodeUri);
    }

    public function getStrokeColor(): string
    {
        if (empty($this->strokeColor)) {
            return '#FF0000';
        }

        return $this->strokeColor;
    }

    public function setStrokeColor(string $strokeColor): void
    {
        $this->strokeColor = $strokeColor;
    }

    public function getStrokeOpacity(): float
    {
        if (empty($this->strokeOpacity)) {
            return 0.8;
        }

        return $this->strokeOpacity;
    }

    public function setStrokeOpacity($strokeOpacity): void
    {
        $this->strokeOpacity = (float)$strokeOpacity;
    }

    public function getStrokeWeight(): int
    {
        if (empty($this->strokeWeight)) {
            return 2;
        }

        return $this->strokeWeight;
    }

    public function setStrokeWeight($strokeWeight): void
    {
        $this->strokeWeight = (int)$strokeWeight;
    }

    public function getFillColor(): string
    {
        if (empty($this->fillColor)) {
            return '#FF0000';
        }

        return $this->fillColor;
    }

    public function setFillColor(string $fillColor): void
    {
        $this->fillColor = $fillColor;
    }

    public function getFillOpacity(): float
    {
        if (empty($this->fillOpacity)) {
            return 0.35;
        }

        return $this->fillOpacity;
    }

    public function setFillOpacity($fillOpacity): void
    {
        $this->fillOpacity = (float)$fillOpacity;
    }

    public function getMarkerIconWidth(): int
    {
        return $this->markerIconWidth;
    }

    public function setMarkerIconWidth($markerIconWidth): void
    {
        $this->markerIconWidth = (int)$markerIconWidth;
    }

    public function getMarkerIconHeight(): int
    {
        return $this->markerIconHeight;
    }

    public function setMarkerIconHeight($markerIconHeight): void
    {
        $this->markerIconHeight = (int)$markerIconHeight;
    }

    public function getMarkerIconAnchorPosX(): int
    {
        return $this->markerIconAnchorPosX;
    }

    public function setMarkerIconAnchorPosX($markerIconAnchorPosX): void
    {
        $this->markerIconAnchorPosX = (int)$markerIconAnchorPosX;
    }

    public function getMarkerIconAnchorPosY(): int
    {
        return $this->markerIconAnchorPosY;
    }

    public function setMarkerIconAnchorPosY($markerIconAnchorPosY): void
    {
        $this->markerIconAnchorPosY = (int)$markerIconAnchorPosY;
    }
}
