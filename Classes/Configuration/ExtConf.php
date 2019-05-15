<?php
declare(strict_types = 1);
namespace JWeiland\Maps2\Configuration;

/*
 * This file is part of the maps2 project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\SingletonInterface;

/**
 * This class will streamline the values from extension manager configuration
 */
class ExtConf implements SingletonInterface
{
    // general
    protected $mapProvider = '';
    protected $defaultMapProvider = '';
    protected $defaultCountry = '';
    protected $defaultLatitude;
    protected $defaultLongitude;
    protected $defaultRadius = 0;
    protected $explicitAllowMapProviderRequests = false;
    protected $explicitAllowMapProviderRequestsBySessionOnly = false;
    protected $infoWindowContentTemplatePath = '';
    protected $allowMapTemplatePath = '';

    // Google Maps
    protected $googleMapsLibrary = '';
    protected $googleMapsGeocodeUri = '';
    protected $googleMapsJavaScriptApiKey = '';
    protected $googleMapsGeocodeApiKey = '';

    // Open Street Map
    protected $openStreetMapGeocodeUri = '';

    // Design/Color
    protected $strokeColor = '';
    protected $strokeOpacity;
    protected $strokeWeight = 0;
    protected $fillColor = '';
    protected $fillOpacity;
    protected $markerIconWidth = 0;
    protected $markerIconHeight = 0;
    protected $markerIconAnchorPosX = 0;
    protected $markerIconAnchorPosY = 0;

    public function __construct()
    {
        // On a fresh installation this value can be null.
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['maps2'])) {
            // get global configuration
            $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['maps2']);
            if (is_array($extConf) && count($extConf)) {
                // call setter method foreach configuration entry
                foreach ($extConf as $key => $value) {
                    $methodName = 'set' . ucfirst($key);
                    if (method_exists($this, $methodName)) {
                        $this->$methodName($value);
                    }
                }
            }
        }
    }

    public function getMapProvider(): string
    {
        if (empty($this->mapProvider)) {
            $this->mapProvider = 'both';
        }

        return $this->mapProvider;
    }

    public function setMapProvider(string $mapProvider)
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

    public function setDefaultMapProvider(string $defaultMapProvider)
    {
        $this->defaultMapProvider = $defaultMapProvider;
    }

    public function getDefaultCountry(): string
    {
        return $this->defaultCountry;
    }

    public function setDefaultCountry(string $defaultCountry)
    {
        $this->defaultCountry = trim($defaultCountry);
    }

    public function getDefaultLatitude(): float
    {
        if (empty($this->defaultLatitude)) {
            return 0.00;
        } else {
            return $this->defaultLatitude;
        }
    }

    public function setDefaultLatitude($defaultLatitude)
    {
        $this->defaultLatitude = (float)$defaultLatitude;
    }

    public function getDefaultLongitude(): float
    {
        if (empty($this->defaultLongitude)) {
            return 0.00;
        } else {
            return $this->defaultLongitude;
        }
    }

    public function setDefaultLongitude($defaultLongitude)
    {
        $this->defaultLongitude = (float)$defaultLongitude;
    }

    public function getDefaultRadius(): int
    {
        if (empty($this->defaultRadius)) {
            return 250;
        } else {
            return $this->defaultRadius;
        }
    }

    public function setDefaultRadius($defaultRadius)
    {
        $this->defaultRadius = (int)$defaultRadius;
    }

    public function getExplicitAllowMapProviderRequests(): bool
    {
        return $this->explicitAllowMapProviderRequests;
    }

    public function setExplicitAllowMapProviderRequests($explicitAllowMapProviderRequests)
    {
        $this->explicitAllowMapProviderRequests = (bool)$explicitAllowMapProviderRequests;
    }

    public function getExplicitAllowMapProviderRequestsBySessionOnly(): bool
    {
        return $this->explicitAllowMapProviderRequestsBySessionOnly;
    }

    public function setExplicitAllowMapProviderRequestsBySessionOnly($explicitAllowMapProviderRequestsBySessionOnly)
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

    public function setInfoWindowContentTemplatePath(string $infoWindowContentTemplatePath)
    {
        $this->infoWindowContentTemplatePath = $infoWindowContentTemplatePath;
    }

    public function getAllowMapTemplatePath(): string
    {
        if (empty($this->allowMapTemplatePath)) {
            $this->allowMapTemplatePath = 'EXT:maps2/Resources/Private/Templates/AllowMapForm.html';
        }
        return $this->allowMapTemplatePath;
    }

    public function setAllowMapTemplatePath(string $allowMapTemplatePath)
    {
        $this->allowMapTemplatePath = $allowMapTemplatePath;
    }

    public function getGoogleMapsLibrary(): string
    {
        if (trim($this->googleMapsLibrary) === '|') {
            $library = 'https://maps.googleapis.com/maps/api/js?key=|&libraries=places';
        } else {
            $library = $this->googleMapsLibrary;
        }

        if (!empty($library)) {
            // insert ApiKey
            $library = str_replace('|', $this->getGoogleMapsJavaScriptApiKey(), $library);
            // $parts: 0 = full string; 1 = s or empty; 2 = needed url
            if (preg_match('|^http(s)?://(.*)$|i', $library, $parts)) {
                return 'https://' . $parts[2];
            }
        }
        return '';
    }

    public function setGoogleMapsLibrary(string $googleMapsLibrary)
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

    public function setGoogleMapsGeocodeUri(string $googleMapsGeocodeUri)
    {
        $this->googleMapsGeocodeUri = trim($googleMapsGeocodeUri);
    }

    public function getGoogleMapsJavaScriptApiKey(): string
    {
        return $this->googleMapsJavaScriptApiKey;
    }

    public function setGoogleMapsJavaScriptApiKey(string $googleMapsJavaScriptApiKey)
    {
        $this->googleMapsJavaScriptApiKey = trim($googleMapsJavaScriptApiKey);
    }

    public function getGoogleMapsGeocodeApiKey(): string
    {
        return $this->googleMapsGeocodeApiKey;
    }

    public function setGoogleMapsGeocodeApiKey(string $googleMapsGeocodeApiKey)
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

    public function setOpenStreetMapGeocodeUri(string $openStreetMapGeocodeUri)
    {
        $this->openStreetMapGeocodeUri = trim($openStreetMapGeocodeUri);
    }

    public function getStrokeColor(): string
    {
        if (empty($this->strokeColor)) {
            return '#FF0000';
        } else {
            return $this->strokeColor;
        }
    }

    public function setStrokeColor(string $strokeColor)
    {
        $this->strokeColor = $strokeColor;
    }

    public function getStrokeOpacity(): float
    {
        if (empty($this->strokeOpacity)) {
            return 0.8;
        } else {
            return $this->strokeOpacity;
        }
    }

    public function setStrokeOpacity($strokeOpacity)
    {
        $this->strokeOpacity = (float)$strokeOpacity;
    }

    public function getStrokeWeight(): int
    {
        if (empty($this->strokeWeight)) {
            return 2;
        } else {
            return $this->strokeWeight;
        }
    }

    public function setStrokeWeight($strokeWeight)
    {
        $this->strokeWeight = (int)$strokeWeight;
    }

    public function getFillColor(): string
    {
        if (empty($this->fillColor)) {
            return '#FF0000';
        } else {
            return $this->fillColor;
        }
    }

    public function setFillColor(string $fillColor)
    {
        $this->fillColor = $fillColor;
    }

    public function getFillOpacity(): float
    {
        if (empty($this->fillOpacity)) {
            return 0.35;
        } else {
            return $this->fillOpacity;
        }
    }

    public function setFillOpacity($fillOpacity)
    {
        $this->fillOpacity = (float)$fillOpacity;
    }

    public function getMarkerIconWidth(): int
    {
        return $this->markerIconWidth;
    }

    public function setMarkerIconWidth($markerIconWidth)
    {
        $this->markerIconWidth = (int)$markerIconWidth;
    }

    public function getMarkerIconHeight(): int
    {
        return $this->markerIconHeight;
    }

    public function setMarkerIconHeight($markerIconHeight)
    {
        $this->markerIconHeight = (int)$markerIconHeight;
    }

    public function getMarkerIconAnchorPosX(): int
    {
        return $this->markerIconAnchorPosX;
    }

    public function setMarkerIconAnchorPosX($markerIconAnchorPosX)
    {
        $this->markerIconAnchorPosX = (int)$markerIconAnchorPosX;
    }

    public function getMarkerIconAnchorPosY(): int
    {
        return $this->markerIconAnchorPosY;
    }

    public function setMarkerIconAnchorPosY($markerIconAnchorPosY)
    {
        $this->markerIconAnchorPosY = (int)$markerIconAnchorPosY;
    }
}
